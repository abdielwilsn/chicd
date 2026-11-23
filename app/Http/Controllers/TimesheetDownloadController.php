<?php

namespace App\Http\Controllers;

use App\Actions\Timesheets\BuildMonthlyTimesheet;
use App\Actions\Timesheets\DownloadTimesheets;
use App\Concerns\ResolvesRequestedMonth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TimesheetDownloadController extends Controller
{
    use ResolvesRequestedMonth;

    /**
     * Download the staff member's own approved timesheet for a month. A month
     * that has not been approved has no timesheet to download yet.
     */
    public function __invoke(Request $request, string $month, string $format, BuildMonthlyTimesheet $buildTimesheet, DownloadTimesheets $download): Response|StreamedResponse
    {
        $month = $this->monthFromPath($month);
        $user = $request->user();
        $review = $user->timesheets()->forMonth($month)->with('approver')->first();

        abort_unless($review?->isApproved(), 404);

        return $download->handle($format, $month, [[
            'user' => $user,
            'review' => $review,
            'timesheet' => $buildTimesheet->handle($user, $month),
        ]], sprintf('chicd-timesheet-%s-%s', Str::slug($user->name), $month->format('Y-m')));
    }
}
