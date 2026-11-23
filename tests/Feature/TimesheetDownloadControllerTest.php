<?php

use App\Models\FieldActivity;
use App\Models\OfficeAttendance;
use App\Models\Timesheet;
use App\Models\User;

beforeEach(function () {
    $this->travelTo('2026-12-05 10:00:00');
    $this->user = User::factory()->create(['name' => 'Ada Okon']);
    OfficeAttendance::factory()->for($this->user)->clockedOut()->create([
        'clocked_in_at' => '2026-11-17 07:00:00',
        'clocked_out_at' => '2026-11-17 15:30:00',
    ]);
    FieldActivity::factory()->for($this->user)->ended()->create([
        'programme' => 'Show Up Ready to Learn (SURL)',
        'activity' => 'Uniform distribution',
        'location' => 'Ikot Ekpene',
        'started_at' => '2026-11-18 08:00:00',
        'ended_at' => '2026-11-18 10:15:00',
    ]);
});

test('staff can download their approved timesheet as CSV', function () {
    Timesheet::factory()->for($this->user)->approved()->create(['month' => '2026-11-01']);

    $response = $this->actingAs($this->user)->get(route('attendance.timesheet.download', ['month' => '2026-11', 'format' => 'csv']));

    $response->assertOk()->assertDownload('chicd-timesheet-ada-okon-2026-11.csv');
    $csv = $response->streamedContent();

    expect($csv)
        ->toContain('Staff,Email,"Timesheet status",Date,Type')
        ->toContain('"Ada Okon",'.$this->user->email.',Approved,2026-11-17,Office,')
        ->toContain(',08:00,16:30,510,8.50,')
        ->toContain('2026-11-18,Field,"Show Up Ready to Learn (SURL)","Uniform distribution","Ikot Ekpene",09:00,11:15,135,2.25,');
});

test('staff can download their approved timesheet as PDF', function () {
    Timesheet::factory()->for($this->user)->approved()->create(['month' => '2026-11-01']);

    $response = $this->actingAs($this->user)->get(route('attendance.timesheet.download', ['month' => '2026-11', 'format' => 'pdf']));

    $response->assertOk()->assertDownload('chicd-timesheet-ada-okon-2026-11.pdf');
    expect($response->headers->get('content-type'))->toBe('application/pdf');
});

test('a month that is not approved has no timesheet to download', function () {
    Timesheet::factory()->for($this->user)->create(['month' => '2026-11-01']);

    $this->actingAs($this->user)->get(route('attendance.timesheet.download', ['month' => '2026-11', 'format' => 'csv']))->assertNotFound();
    $this->actingAs($this->user)->get(route('attendance.timesheet.download', ['month' => '2026-10', 'format' => 'csv']))->assertNotFound();
});

test('staff only ever download their own timesheet', function () {
    Timesheet::factory()->approved()->create(['month' => '2026-11-01']);

    $this->actingAs($this->user)->get(route('attendance.timesheet.download', ['month' => '2026-11', 'format' => 'csv']))->assertNotFound();
});

test('a future month is not found', function () {
    $this->actingAs($this->user)->get('/my-attendance/2027-01/timesheet.csv')->assertNotFound();
});
