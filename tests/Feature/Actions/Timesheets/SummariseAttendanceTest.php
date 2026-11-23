<?php

use App\Actions\Timesheets\BuildMonthlyTimesheet;
use App\Actions\Timesheets\SummariseAttendance;
use App\Models\FieldActivity;
use App\Models\LeaveRequest;
use App\Models\OfficeAttendance;
use App\Models\PublicHoliday;
use App\Models\User;
use Carbon\CarbonImmutable;

/**
 * Summarise November 2026 for a staff member. November 2026 starts on a
 * Sunday and has 21 weekdays. Lagos is UTC+1, so 07:15 UTC is 08:15 there.
 *
 * @return array<string, mixed>
 */
function summariseNovember(User $user): array
{
    $month = CarbonImmutable::create(2026, 11, 1, 0, 0, 0, 'Africa/Lagos');

    return app(SummariseAttendance::class)->handle($user, $month, app(BuildMonthlyTimesheet::class)->handle($user, $month));
}

beforeEach(function () {
    config(['chicd.working_hours' => ['days' => [1, 2, 3, 4, 5], 'start' => '08:00', 'grace_minutes' => 15]]);
    $this->travelTo('2026-12-05 10:00:00');
    $this->staff = User::factory()->create(['created_at' => '2026-10-01 09:00:00']);
});

test('starting after the start time plus the grace period is late, within it is not', function () {
    OfficeAttendance::factory()->for($this->staff)->clockedOut()->create(['clocked_in_at' => '2026-11-02 07:15:00', 'clocked_out_at' => '2026-11-02 15:00:00']);
    OfficeAttendance::factory()->for($this->staff)->clockedOut()->create(['clocked_in_at' => '2026-11-03 07:16:00', 'clocked_out_at' => '2026-11-03 15:00:00']);
    FieldActivity::factory()->for($this->staff)->ended()->create(['started_at' => '2026-11-04 09:30:00', 'ended_at' => '2026-11-04 12:00:00']);

    $summary = summariseNovember($this->staff);

    expect($summary['lateDays'])->toBe([
        ['date' => '2026-11-03', 'label' => 'Tuesday 3 November', 'startedAt' => '08:16'],
        ['date' => '2026-11-04', 'label' => 'Wednesday 4 November', 'startedAt' => '10:30'],
    ]);
});

test('the earliest start of the day decides lateness when office and field work are mixed', function () {
    FieldActivity::factory()->for($this->staff)->ended()->create(['started_at' => '2026-11-02 06:45:00', 'ended_at' => '2026-11-02 09:00:00']);
    OfficeAttendance::factory()->for($this->staff)->clockedOut()->create(['clocked_in_at' => '2026-11-02 09:30:00', 'clocked_out_at' => '2026-11-02 15:00:00']);

    expect(summariseNovember($this->staff)['lateDays'])->toBe([]);
});

test('a weekday with no record and no leave is an absence, weekends are not working days', function () {
    $summary = summariseNovember($this->staff);

    expect($summary['workingDays'])->toBe(21)
        ->and($summary['absentDays'])->toHaveCount(21)
        ->and($summary['absentDays'][0]['date'])->toBe('2026-11-02');
});

test('approved leave counts as leave, not absence, while pending leave does not count yet', function () {
    LeaveRequest::factory()->for($this->staff)->approved()->create(['starts_on' => '2026-11-02', 'ends_on' => '2026-11-06']);
    LeaveRequest::factory()->for($this->staff)->create(['starts_on' => '2026-11-09', 'ends_on' => '2026-11-09']);

    $summary = summariseNovember($this->staff);
    $absentDates = array_column($summary['absentDays'], 'date');

    expect(array_column($summary['leaveDays'], 'date'))->toBe(['2026-11-02', '2026-11-03', '2026-11-04', '2026-11-05', '2026-11-06'])
        ->and($summary['leaveDays'][0]['type'])->toBe('Annual leave')
        ->and($absentDates)->not->toContain('2026-11-02')
        ->and($absentDates)->toContain('2026-11-09');
});

test('a public holiday is not a working day and no one is absent on it', function () {
    PublicHoliday::factory()->create(['date' => '2026-11-02', 'name' => 'Staff Day']);

    $summary = summariseNovember($this->staff);

    expect($summary['workingDays'])->toBe(20)
        ->and(array_column($summary['absentDays'], 'date'))->not->toContain('2026-11-02')
        ->and($summary['holidays'])->toBe([['date' => '2026-11-02', 'label' => 'Monday 2 November', 'name' => 'Staff Day']]);
});

test('days before the staff member joined or after they left are not counted', function () {
    $joinedMidMonth = User::factory()->create(['created_at' => '2026-11-23 09:00:00', 'deactivated_at' => '2026-11-25 16:00:00']);

    $summary = summariseNovember($joinedMidMonth);

    expect($summary['workingDays'])->toBe(3)
        ->and(array_column($summary['absentDays'], 'date'))->toBe(['2026-11-23', '2026-11-24', '2026-11-25']);
});

test('today only counts as an absence once it is over', function () {
    $this->travelTo('2026-11-04 10:00:00');

    $absentDates = array_column(summariseNovember($this->staff)['absentDays'], 'date');

    expect($absentDates)->toBe(['2026-11-02', '2026-11-03']);
});
