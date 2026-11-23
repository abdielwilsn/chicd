@php
    $hours = fn (int $minutes): string => sprintf('%dh %02dm', intdiv($minutes, 60), $minutes % 60);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $isConsolidated ? 'Consolidated timesheet' : 'Timesheet' }} — {{ $monthLabel }}</title>
    <style>
        @page { margin: 28px 32px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #12241b; }
        h1 { font-size: 18px; margin: 0 0 2px; }
        h2 { font-size: 14px; margin: 0 0 2px; }
        .muted { color: #5b6b62; }
        .organisation { font-size: 11px; font-weight: bold; color: #1d5c3f; text-transform: uppercase; letter-spacing: 1px; }
        .header { border-bottom: 3px solid #f2b31b; padding-bottom: 8px; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th { text-align: left; background: #e9f1ec; padding: 5px 6px; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; }
        td { padding: 4px 6px; border-bottom: 1px solid #dfe6e1; vertical-align: top; }
        .number { text-align: right; white-space: nowrap; }
        .flag { color: #9a5b00; }
        .status { display: inline-block; padding: 2px 6px; font-weight: bold; }
        .approved { background: #dff1e5; color: #1d5c3f; }
        .pending { background: #fdf0cf; color: #7a4d00; }
        .totals td { font-weight: bold; border-bottom: none; }
        .sheet { page-break-after: always; }
        .sheet:last-child { page-break-after: auto; }
        .footer { margin-top: 14px; }
    </style>
</head>
<body>
    @if ($isConsolidated)
        <div class="sheet">
            <div class="header">
                <div class="organisation">{{ $organisation }}</div>
                <h1>Consolidated timesheet — {{ $monthLabel }}</h1>
                <div class="muted">Generated {{ $generatedAt }}. Office and field time are shown separately.</div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Staff member</th>
                        <th class="number">Office time</th>
                        <th class="number">Office days</th>
                        <th class="number">Field time</th>
                        <th class="number">Field days</th>
                        <th class="number">Days worked</th>
                        <th class="number">Flagged clockings</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sheets as $sheet)
                        <tr>
                            <td>{{ $sheet['name'] }}</td>
                            <td class="number">{{ $hours($sheet['totals']['officeMinutes']) }}</td>
                            <td class="number">{{ $sheet['totals']['officeDays'] }}</td>
                            <td class="number">{{ $hours($sheet['totals']['fieldMinutes']) }}</td>
                            <td class="number">{{ $sheet['totals']['fieldDays'] }}</td>
                            <td class="number">{{ $sheet['totals']['daysWorked'] }}</td>
                            <td class="number">{{ $sheet['totals']['flaggedClockings'] }}</td>
                            <td><span class="status {{ $sheet['isApproved'] ? 'approved' : 'pending' }}">{{ $sheet['isApproved'] ? 'Approved' : 'Not yet approved' }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @foreach ($sheets as $sheet)
        <div class="sheet">
            <div class="header">
                <div class="organisation">{{ $organisation }}</div>
                <h1>Monthly timesheet — {{ $monthLabel }}</h1>
                <h2>{{ $sheet['name'] }} <span class="muted">· {{ $sheet['email'] }}</span></h2>
                <span class="status {{ $sheet['isApproved'] ? 'approved' : 'pending' }}">{{ $sheet['approval'] }}</span>
            </div>

            @if ($sheet['lines'] === [])
                <p class="muted">Nothing was recorded this month.</p>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Office or programme</th>
                            <th>Activity</th>
                            <th>Location</th>
                            <th>Start</th>
                            <th>End</th>
                            <th class="number">Time</th>
                            <th>Flag</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sheet['lines'] as $line)
                            <tr>
                                <td>{{ \Carbon\CarbonImmutable::parse($line['date'])->format('D j M') }}</td>
                                <td>{{ $line['type'] }}</td>
                                <td>{{ $line['where'] }}</td>
                                <td>{{ $line['activity'] }}</td>
                                <td>{{ $line['location'] }}</td>
                                <td>{{ $line['start'] }}</td>
                                <td>{{ $line['end'] }}</td>
                                <td class="number">{{ $line['minutes'] === null ? '—' : $hours($line['minutes']) }}</td>
                                <td class="flag">{{ $line['flag'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <table class="footer">
                <tr class="totals">
                    <td>Office time: {{ $hours($sheet['totals']['officeMinutes']) }} over {{ $sheet['totals']['officeDays'] }} {{ Str::plural('day', $sheet['totals']['officeDays']) }}</td>
                    <td>Field time: {{ $hours($sheet['totals']['fieldMinutes']) }} over {{ $sheet['totals']['fieldDays'] }} {{ Str::plural('day', $sheet['totals']['fieldDays']) }}</td>
                    <td>Days worked: {{ $sheet['totals']['daysWorked'] }}</td>
                </tr>
            </table>
            <p class="muted">Open records are listed but not counted. Generated {{ $generatedAt }}.</p>
        </div>
    @endforeach
</body>
</html>
