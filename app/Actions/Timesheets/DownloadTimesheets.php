<?php

namespace App\Actions\Timesheets;

use App\Models\FieldActivity;
use App\Models\OfficeAttendance;
use App\Models\Timesheet;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Http\Response;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Downloads one or more monthly timesheets as a PDF or a CSV file. One
 * timesheet is an individual timesheet; several make a consolidated one.
 *
 * @phpstan-import-type MonthlyTimesheet from BuildMonthlyTimesheet
 *
 * @phpstan-type TimesheetSheet array{user: User, review: Timesheet|null, timesheet: MonthlyTimesheet}
 * @phpstan-type TimesheetLine array{date: string, type: string, where: string, activity: string, location: string, start: string, end: string, minutes: int|null, flag: string}
 */
class DownloadTimesheets
{
    /**
     * @param  list<TimesheetSheet>  $sheets
     */
    public function handle(string $format, CarbonImmutable $month, array $sheets, string $filename): Response|StreamedResponse
    {
        return $format === 'pdf'
            ? $this->pdf($month, $sheets, $filename)
            : $this->csv($sheets, $filename);
    }

    /**
     * @param  list<TimesheetSheet>  $sheets
     */
    private function pdf(CarbonImmutable $month, array $sheets, string $filename): Response
    {
        $timezone = config('chicd.timezone');

        return Pdf::loadView('timesheets.pdf', [
            'organisation' => config('chicd.name'),
            'monthLabel' => $month->format('F Y'),
            'isConsolidated' => count($sheets) > 1,
            'generatedAt' => now($timezone)->format('j F Y, H:i'),
            'sheets' => array_map(fn (array $sheet): array => [
                'name' => $sheet['user']->name,
                'email' => $sheet['user']->email,
                'approval' => $sheet['review']?->isApproved()
                    ? sprintf('Approved by %s on %s', $sheet['review']->approver->name ?? 'an admin', $sheet['review']->approved_at?->setTimezone($timezone)->format('j F Y'))
                    : 'Not yet approved',
                'isApproved' => (bool) $sheet['review']?->isApproved(),
                'totals' => $sheet['timesheet']['totals'],
                'lines' => $this->lines($sheet['timesheet']),
            ], $sheets),
        ])->setPaper('a4', 'landscape')->download($filename.'.pdf');
    }

    /**
     * @param  list<TimesheetSheet>  $sheets
     */
    private function csv(array $sheets, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($sheets): void {
            $output = fopen('php://output', 'w');

            if ($output === false) {
                throw new RuntimeException('Could not open the output stream for the timesheet CSV.');
            }

            // A byte-order mark so Excel opens the file as UTF-8.
            fwrite($output, "\u{FEFF}");
            fputcsv($output, ['Staff', 'Email', 'Timesheet status', 'Date', 'Type', 'Office or programme', 'Activity', 'Location', 'Start', 'End', 'Minutes', 'Hours', 'Flag'], escape: '');

            foreach ($sheets as $sheet) {
                $status = $sheet['review']?->isApproved() ? 'Approved' : 'Not yet approved';

                foreach ($this->lines($sheet['timesheet']) as $line) {
                    fputcsv($output, [
                        $sheet['user']->name,
                        $sheet['user']->email,
                        $status,
                        $line['date'],
                        $line['type'],
                        $line['where'],
                        $line['activity'],
                        $line['location'],
                        $line['start'],
                        $line['end'],
                        $line['minutes'],
                        $line['minutes'] === null ? null : number_format($line['minutes'] / 60, 2, '.', ''),
                        $line['flag'],
                    ], escape: '');
                }
            }

            fclose($output);
        }, $filename.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * One line per office session or field activity, in date order.
     *
     * @param  MonthlyTimesheet  $timesheet
     * @return list<TimesheetLine>
     */
    private function lines(array $timesheet): array
    {
        $timezone = config('chicd.timezone');
        $lines = [];

        foreach ($timesheet['days'] as $day) {
            foreach ($day['officeAttendances'] as $attendance) {
                /** @var OfficeAttendance $attendance */
                $lines[] = [
                    'date' => $day['date'],
                    'type' => 'Office',
                    'where' => $attendance->office->name ?? 'Office',
                    'activity' => '',
                    'location' => '',
                    'start' => $attendance->clocked_in_at->setTimezone($timezone)->format('H:i'),
                    'end' => $attendance->clocked_out_at?->setTimezone($timezone)->format('H:i') ?? 'Not clocked out',
                    'minutes' => BuildMonthlyTimesheet::officeMinutes($attendance),
                    'flag' => BuildMonthlyTimesheet::isFlagged($attendance) ? 'Outside office area' : '',
                ];
            }

            foreach ($day['fieldActivities'] as $activity) {
                /** @var FieldActivity $activity */
                $lines[] = [
                    'date' => $day['date'],
                    'type' => 'Field',
                    'where' => $activity->programme,
                    'activity' => $activity->activity,
                    'location' => $activity->location,
                    'start' => $activity->started_at->setTimezone($timezone)->format('H:i'),
                    'end' => $activity->ended_at?->setTimezone($timezone)->format('H:i') ?? 'Not ended',
                    'minutes' => BuildMonthlyTimesheet::fieldMinutes($activity),
                    'flag' => '',
                ];
            }
        }

        return $lines;
    }
}
