<?php

use App\Enums\PayrollStatus;
use App\Models\Office;
use App\Models\OfficeAttendance;
use App\Models\PayrollRecord;
use App\Models\Timesheet;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->travelTo('2026-12-05 10:00:00');
    $this->admin = User::factory()->admin()->create(['name' => 'Zara Admin', 'created_at' => '2026-10-01 09:00:00']);
    $this->staff = User::factory()->create(['name' => 'Ada Okon', 'created_at' => '2026-10-01 09:00:00']);
    $this->staff->monthly_salary = 15000000;
    $this->staff->save();
});

test('staff cannot see payroll', function () {
    $this->actingAs($this->staff)->get(route('admin.payroll.index'))->assertForbidden();
});

test('a staff member\'s salary never reaches their own browser', function () {
    $this->actingAs($this->staff)->get(route('dashboard'))->assertInertia(fn (Assert $page) => $page
        ->missing('auth.user.monthly_salary')
    );
});

test('salaries and adjustments are encrypted at rest', function () {
    PayrollRecord::factory()->for($this->staff)->create(['month' => '2026-11-01', 'attendance_adjustment' => -500000]);

    expect(DB::table('users')->where('id', $this->staff->id)->value('monthly_salary'))->not->toContain('15000000')
        ->and(DB::table('payroll_records')->value('attendance_adjustment'))->not->toContain('500000');
});

test('admins see each staff member\'s salary, adjustment and approved pay with their attendance as a guide', function () {
    PayrollRecord::factory()->for($this->staff)->create(['month' => '2026-11-01', 'attendance_adjustment' => -500000, 'adjustment_note' => '1 unexplained absence']);

    $this->actingAs($this->admin)->get(route('admin.payroll.index', ['month' => '2026-11']))->assertInertia(fn (Assert $page) => $page
        ->component('admin/payroll/Index')
        ->where('staff.0.name', 'Ada Okon')
        ->where('staff.0.monthlySalary', '₦150,000.00')
        ->where('staff.0.adjustmentLabel', '-₦5,000.00')
        ->where('staff.0.approvedPay', '₦145,000.00')
        ->where('staff.0.status', 'pending')
        ->where('staff.0.attendance.absentDays', 21)
        ->where('staff.0.approvalBlockers', ['The timesheet for this month is not approved yet.'])
    );
});

test('an admin saves a deduction with its reason, and a zero adjustment clears it', function () {
    $month = ['user' => $this->staff, 'month' => '2026-11'];

    $this->actingAs($this->admin)->patch(route('admin.payroll.update', $month), ['adjustment' => '-5000.50', 'note' => '1 unexplained absence'])
        ->assertSessionHasNoErrors();

    $record = PayrollRecord::sole();
    expect($record->attendance_adjustment)->toBe(-500050)
        ->and($record->adjustment_note)->toBe('1 unexplained absence')
        ->and($record->approvedPay($this->staff->monthly_salary))->toBe(14499950);

    $this->actingAs($this->admin)->patch(route('admin.payroll.update', $month), ['adjustment' => '0'])->assertSessionHasNoErrors();

    expect($record->refresh()->attendance_adjustment)->toBeNull()
        ->and($record->adjustment_note)->toBeNull();
});

test('an adjustment needs a reason', function () {
    $this->actingAs($this->admin)->patch(route('admin.payroll.update', ['user' => $this->staff, 'month' => '2026-11']), ['adjustment' => '-5000'])
        ->assertSessionHasErrors(['note' => 'Please note the reason for the adjustment, for example "2 unexplained absences".']);

    $this->assertDatabaseEmpty('payroll_records');
});

test('pay cannot be approved until the timesheet is approved and a salary is set', function () {
    $this->staff->monthly_salary = null;
    $this->staff->save();

    $this->actingAs($this->admin)->post(route('admin.payroll.approve', ['user' => $this->staff, 'month' => '2026-11']))
        ->assertSessionHasErrors(['payroll']);

    $this->assertDatabaseEmpty('payroll_records');
});

test('approved pay keeps the salary it was approved with after a pay rise', function () {
    Timesheet::factory()->for($this->staff)->approved()->create(['month' => '2026-11-01']);

    $this->actingAs($this->admin)->post(route('admin.payroll.approve', ['user' => $this->staff, 'month' => '2026-11']))->assertSessionHasNoErrors();
    $this->actingAs($this->admin)->put(route('admin.staff.salary', $this->staff), ['monthly_salary' => '200000']);

    $record = PayrollRecord::sole();
    expect($record->monthly_salary)->toBe(15000000)
        ->and($record->approved_by)->toBe($this->admin->id)
        ->and($this->staff->refresh()->monthly_salary)->toBe(20000000);
});

test('only approved pay can be marked as paid, and paid pay cannot be moved back', function () {
    $pending = PayrollRecord::factory()->for($this->staff)->create(['month' => '2026-11-01']);

    $this->actingAs($this->admin)->post(route('admin.payroll.pay', ['user' => $this->staff, 'month' => '2026-11']))->assertConflict();
    expect($pending->refresh()->status)->toBe(PayrollStatus::Pending);

    $pending->forceFill(['status' => PayrollStatus::Paid])->save();
    $this->actingAs($this->admin)->delete(route('admin.payroll.reopen', ['user' => $this->staff, 'month' => '2026-11']))->assertConflict();
});

test('approved pay can be moved back to Pending and changed', function () {
    PayrollRecord::factory()->for($this->staff)->approved()->create(['month' => '2026-11-01']);

    $this->actingAs($this->admin)->delete(route('admin.payroll.reopen', ['user' => $this->staff, 'month' => '2026-11']))->assertRedirect();

    $record = PayrollRecord::sole();
    expect($record->status)->toBe(PayrollStatus::Pending)
        ->and($record->monthly_salary)->toBeNull();
});

test('a timesheet cannot be reopened once its pay is approved', function () {
    Timesheet::factory()->for($this->staff)->approved()->create(['month' => '2026-11-01']);
    PayrollRecord::factory()->for($this->staff)->approved()->create(['month' => '2026-11-01']);

    $this->actingAs($this->admin)->delete(route('admin.timesheets.reopen', ['user' => $this->staff, 'month' => '2026-11']), ['reason' => 'Fix a record'])
        ->assertSessionHasErrors(['reason' => 'The pay for this month is already approved or paid. Move the pay back to Pending on the Payroll page first.']);
});

test('acceptance: recorded work becomes a timesheet that is corrected, approved, downloaded and paid', function () {
    Office::factory()->create(['latitude' => 5.0377, 'longitude' => 7.9128, 'geofence_radius_metres' => 100]);
    $atOffice = ['latitude' => 5.0377, 'longitude' => 7.9128, 'accuracy' => 10];

    // One office day: Ada clocks in but forgets to clock out.
    $this->travelTo('2026-11-02 07:00:00');
    $this->actingAs($this->staff)->post(route('office-attendances.store'), $atOffice)->assertSessionHasNoErrors();

    // One day with two field activities.
    foreach ([['2026-11-03 08:00:00', '2026-11-03 10:00:00'], ['2026-11-03 11:00:00', '2026-11-03 14:00:00']] as [$start, $end]) {
        $this->travelTo($start);
        $this->actingAs($this->staff)->post(route('field-activities.store'), [
            'programme' => config('chicd.field_programmes')[0],
            'activity' => 'School visit',
            'location' => 'Ikot Ekpene',
            ...$atOffice,
        ])->assertSessionHasNoErrors();
        $this->travelTo($end);
        $activity = $this->staff->fieldActivities()->open()->sole();
        $this->actingAs($this->staff)->patch(route('field-activities.update', $activity), $atOffice)->assertSessionHasNoErrors();
    }

    // The month's timesheet shows both days with no manual entry.
    $this->travelTo('2026-12-02 09:00:00');
    $this->actingAs($this->staff)->get(route('attendance.index', ['month' => '2026-11']))->assertInertia(fn (Assert $page) => $page
        ->has('days', 2)
        ->where('days.0.date', '2026-11-03')
        ->has('days.0.fieldActivities', 2)
        ->where('days.1.date', '2026-11-02')
        ->has('days.1.officeAttendances', 1)
    );

    // Ada asks for a correction and an admin applies it.
    $attendance = OfficeAttendance::sole();
    $this->actingAs($this->staff)->post(route('attendance.corrections.store'), [
        'record_type' => 'office_attendance',
        'record_id' => $attendance->id,
        'details' => 'I forgot to clock out. I left at 16:00.',
    ])->assertSessionHasNoErrors();
    $correction = $this->staff->correctionRequests()->sole();
    $this->actingAs($this->admin)->patch(route('admin.office-attendances.update', $attendance), [
        'clocked_in_at' => '2026-11-02T08:00',
        'clocked_out_at' => '2026-11-02T16:00',
        'reason' => 'Forgot to clock out; confirmed with line manager.',
        'correction_request_id' => $correction->id,
    ])->assertSessionHasNoErrors();

    // The audit trail shows the change, and the admin approves the month.
    $this->actingAs($this->admin)->get(route('admin.timesheets.show', ['user' => $this->staff, 'month' => '2026-11']))->assertInertia(fn (Assert $page) => $page
        ->where('history.0.action', 'Corrected')
        ->where('history.0.reason', 'Forgot to clock out; confirmed with line manager.')
    );
    $this->actingAs($this->admin)->post(route('admin.timesheets.approve', ['user' => $this->staff, 'month' => '2026-11']))->assertSessionHasNoErrors();

    // Ada downloads her approved timesheet.
    $this->actingAs($this->staff)->get(route('attendance.timesheet.download', ['month' => '2026-11', 'format' => 'csv']))->assertOk()->assertDownload();

    // The admin moves the pay from Pending to Approved to Paid.
    $payroll = ['user' => $this->staff, 'month' => '2026-11'];
    $this->actingAs($this->admin)->post(route('admin.payroll.approve', $payroll))->assertSessionHasNoErrors();
    expect(PayrollRecord::sole()->status)->toBe(PayrollStatus::Approved);
    $this->actingAs($this->admin)->post(route('admin.payroll.pay', $payroll))->assertSessionHasNoErrors();

    $record = PayrollRecord::sole();
    expect($record->status)->toBe(PayrollStatus::Paid)
        ->and($record->paid_by)->toBe($this->admin->id)
        ->and($record->approvedPay())->toBe(15000000);
});
