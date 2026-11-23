<?php

use App\Http\Controllers\Admin\AttendanceOverviewController;
use App\Http\Controllers\Admin\AttendanceRecordController;
use App\Http\Controllers\Admin\CorrectionRequestController as AdminCorrectionRequestController;
use App\Http\Controllers\Admin\DonationController as AdminDonationController;
use App\Http\Controllers\Admin\LeaveRequestController as AdminLeaveRequestController;
use App\Http\Controllers\Admin\MediaItemController as AdminMediaItemController;
use App\Http\Controllers\Admin\OpportunityController as AdminOpportunityController;
use App\Http\Controllers\Admin\PayrollController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\ProgrammeController as AdminProgrammeController;
use App\Http\Controllers\Admin\PublicationController as AdminPublicationController;
use App\Http\Controllers\Admin\PublicHolidayController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\TeamMemberController as AdminTeamMemberController;
use App\Http\Controllers\Admin\TimesheetController;
use App\Http\Controllers\AttendanceHistoryController;
use App\Http\Controllers\CorrectionRequestController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\FieldActivityController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LeadershipController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\NewsletterSubscriptionController;
use App\Http\Controllers\OfficeAttendanceController;
use App\Http\Controllers\OpportunityController;
use App\Http\Controllers\PaystackWebhookController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProgrammeController;
use App\Http\Controllers\PublicationController;
use App\Http\Controllers\TimesheetDownloadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Website
|--------------------------------------------------------------------------
*/

Route::get('/', HomeController::class)->name('home');

Route::prefix('about')->name('about.')->group(function () {
    Route::inertia('/', 'site/about/Index')->name('index');
    Route::inertia('our-story', 'site/about/Story')->name('story');
    Route::inertia('vision-mission-values', 'site/about/Values')->name('values');
    Route::get('leadership-and-team', LeadershipController::class)->name('leadership');
});

Route::prefix('our-work')->name('work.')->group(function () {
    Route::get('/', [ProgrammeController::class, 'index'])->name('index');
    Route::inertia('show-up-ready-to-learn', 'site/work/Surl')->name('surl');
    Route::inertia('safe-housing', 'site/work/SafeHousing')->name('safe-housing');
    Route::get('programmes/{programme:slug}', [ProgrammeController::class, 'show'])->name('programme');
});

Route::prefix('resources')->name('resources.')->group(function () {
    Route::get('research-and-insights', [PublicationController::class, 'research'])->name('research');
    Route::get('publications-and-reports', [PublicationController::class, 'index'])->name('publications');
    Route::get('news-and-stories', [PostController::class, 'index'])->name('news');
    Route::get('news-and-stories/{post:slug}', [PostController::class, 'show'])->name('post');
    Route::get('media-gallery', GalleryController::class)->name('gallery');
});

Route::prefix('get-involved')->name('get-involved.')->group(function () {
    Route::inertia('/', 'site/involved/Index')->name('index');
    Route::inertia('partnerships', 'site/involved/Partnerships')->name('partnerships');
    Route::get('volunteer', [OpportunityController::class, 'volunteer'])->name('volunteer');
    Route::get('opportunities', [OpportunityController::class, 'index'])->name('opportunities');
    Route::get('opportunities/{opportunity:slug}', [OpportunityController::class, 'show'])->name('opportunity');
    Route::inertia('support-our-programmes', 'site/involved/Support')->name('support');
});

Route::inertia('contact', 'site/Contact')->name('contact');
Route::get('donate', [DonationController::class, 'create'])->name('donate');
Route::get('donate/thank-you', [DonationController::class, 'callback'])->name('donations.callback');

Route::prefix('policies')->name('policies.')->group(function () {
    Route::get('/', [PublicationController::class, 'accountability'])->name('index');
    Route::inertia('privacy', 'site/policies/Policy', ['policy' => 'privacy'])->name('privacy');
    Route::inertia('safeguarding', 'site/policies/Policy', ['policy' => 'safeguarding'])->name('safeguarding');
    Route::inertia('terms-of-use', 'site/policies/Policy', ['policy' => 'terms'])->name('terms');
    Route::inertia('complaints-and-feedback', 'site/policies/Feedback')->name('feedback');
});

Route::middleware('throttle:6,1')->group(function () {
    Route::post('enquiries', [EnquiryController::class, 'store'])->name('enquiries.store');
    Route::post('newsletter', [NewsletterSubscriptionController::class, 'store'])->name('newsletter.store');
    Route::post('donations', [DonationController::class, 'store'])->name('donations.store');
});

// Paystack payment events. Excluded from CSRF protection in bootstrap/app.php; the controller checks Paystack's signature instead.
Route::post('webhooks/paystack', PaystackWebhookController::class)->name('webhooks.paystack');

/*
|--------------------------------------------------------------------------
| Staff Portal
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::get('my-attendance', [AttendanceHistoryController::class, 'index'])->name('attendance.index');

    Route::post('office-attendances', [OfficeAttendanceController::class, 'store'])->name('office-attendances.store');
    Route::patch('office-attendances/{officeAttendance}', [OfficeAttendanceController::class, 'update'])
        ->can('update', 'officeAttendance')
        ->name('office-attendances.update');

    Route::post('field-activities', [FieldActivityController::class, 'store'])->name('field-activities.store');
    Route::patch('field-activities/{fieldActivity}', [FieldActivityController::class, 'update'])
        ->can('update', 'fieldActivity')
        ->name('field-activities.update');

    Route::post('my-attendance/corrections', [CorrectionRequestController::class, 'store'])
        ->middleware('throttle:20,1')
        ->name('attendance.corrections.store');
    Route::get('my-leave', [LeaveRequestController::class, 'index'])->name('leave.index');
    Route::post('my-leave', [LeaveRequestController::class, 'store'])->middleware('throttle:20,1')->name('leave.store');
    Route::delete('my-leave/{leaveRequest}', [LeaveRequestController::class, 'cancel'])
        ->can('cancel', 'leaveRequest')
        ->name('leave.cancel');

    Route::get('my-attendance/{month}/timesheet.{format}', TimesheetDownloadController::class)
        ->where(['month' => '\d{4}-\d{2}', 'format' => 'pdf|csv'])
        ->name('attendance.timesheet.download');
});

/*
|--------------------------------------------------------------------------
| Staff Portal: Admin
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'can:access-admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('attendance', [AttendanceOverviewController::class, 'index'])->name('attendance.index');

    Route::get('leave', [AdminLeaveRequestController::class, 'index'])->name('leave.index');
    Route::patch('leave/{leaveRequest}', [AdminLeaveRequestController::class, 'update'])->name('leave.update');
    Route::post('public-holidays', [PublicHolidayController::class, 'store'])->name('holidays.store');
    Route::delete('public-holidays/{publicHoliday}', [PublicHolidayController::class, 'destroy'])->name('holidays.destroy');

    Route::get('payroll', [PayrollController::class, 'index'])->name('payroll.index');
    Route::prefix('staff/{user}/payroll/{month}')->where(['month' => '\d{4}-\d{2}'])->name('payroll.')->group(function () {
        Route::patch('/', [PayrollController::class, 'update'])->name('update');
        Route::post('approval', [PayrollController::class, 'approve'])->name('approve');
        Route::delete('approval', [PayrollController::class, 'reopen'])->name('reopen');
        Route::post('payment', [PayrollController::class, 'markPaid'])->name('pay');
    });

    Route::get('staff', [StaffController::class, 'index'])->name('staff.index');
    Route::post('staff', [StaffController::class, 'store'])->middleware('throttle:20,1')->name('staff.store');
    Route::patch('staff/{user}', [StaffController::class, 'update'])->name('staff.update');
    Route::put('staff/{user}/salary', [StaffController::class, 'updateSalary'])->name('staff.salary');
    Route::post('staff/{user}/password-link', [StaffController::class, 'sendPasswordLink'])
        ->middleware('throttle:10,1')
        ->name('staff.password-link');
    Route::post('staff/{user}/deactivation', [StaffController::class, 'deactivate'])->name('staff.deactivate');
    Route::delete('staff/{user}/deactivation', [StaffController::class, 'reactivate'])->name('staff.reactivate');

    Route::get('timesheets/{month}/consolidated.{format}', [TimesheetController::class, 'consolidated'])
        ->where(['month' => '\d{4}-\d{2}', 'format' => 'pdf|csv'])
        ->name('timesheets.consolidated');

    Route::prefix('staff/{user}/timesheets/{month}')->where(['month' => '\d{4}-\d{2}'])->name('timesheets.')->group(function () {
        Route::get('/', [TimesheetController::class, 'show'])->name('show');
        Route::post('approval', [TimesheetController::class, 'approve'])->name('approve');
        Route::delete('approval', [TimesheetController::class, 'reopen'])->name('reopen');
        Route::get('timesheet.{format}', [TimesheetController::class, 'download'])
            ->where('format', 'pdf|csv')
            ->name('download');
    });

    Route::patch('office-attendances/{officeAttendance}', [AttendanceRecordController::class, 'updateOfficeAttendance'])->name('office-attendances.update');
    Route::patch('field-activities/{fieldActivity}', [AttendanceRecordController::class, 'updateFieldActivity'])->name('field-activities.update');

    Route::resource('publications', AdminPublicationController::class)->except('show');
    Route::resource('programmes', AdminProgrammeController::class)->except('show');
    Route::resource('team', AdminTeamMemberController::class)->parameters(['team' => 'teamMember'])->except('show');
    Route::resource('opportunities', AdminOpportunityController::class)->except('show');
    Route::resource('news', AdminPostController::class)->parameters(['news' => 'post'])->except('show');
    Route::resource('gallery', AdminMediaItemController::class)->parameters(['gallery' => 'mediaItem'])->except('show');

    Route::get('donations', [AdminDonationController::class, 'index'])->name('donations.index');
    Route::get('donations/{month}/donations.csv', [AdminDonationController::class, 'export'])
        ->where('month', '\d{4}-\d{2}')
        ->name('donations.export');

    Route::get('corrections', [AdminCorrectionRequestController::class, 'index'])->name('corrections.index');
    Route::patch('corrections/{correctionRequest}', [AdminCorrectionRequestController::class, 'update'])->name('corrections.update');
});

require __DIR__.'/settings.php';
