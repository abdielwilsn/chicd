<?php

use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\NewsletterSubscriptionController;
use App\Http\Controllers\PublicationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Website
|--------------------------------------------------------------------------
*/

Route::inertia('/', 'site/Home')->name('home');

Route::prefix('about')->name('about.')->group(function () {
    Route::inertia('/', 'site/about/Index')->name('index');
    Route::inertia('our-story', 'site/about/Story')->name('story');
    Route::inertia('vision-mission-values', 'site/about/Values')->name('values');
    Route::inertia('leadership-and-team', 'site/about/Leadership')->name('leadership');
});

Route::prefix('our-work')->name('work.')->group(function () {
    Route::inertia('/', 'site/work/Index')->name('index');
    Route::inertia('show-up-ready-to-learn', 'site/work/Surl')->name('surl');
    Route::inertia('safe-housing', 'site/work/SafeHousing')->name('safe-housing');
});

Route::prefix('resources')->name('resources.')->group(function () {
    Route::inertia('research-and-insights', 'site/resources/Research')->name('research');
    Route::get('publications-and-reports', [PublicationController::class, 'index'])->name('publications');
    Route::inertia('news-and-stories', 'site/resources/News')->name('news');
});

Route::prefix('get-involved')->name('get-involved.')->group(function () {
    Route::inertia('/', 'site/involved/Index')->name('index');
    Route::inertia('partnerships', 'site/involved/Partnerships')->name('partnerships');
    Route::inertia('volunteer', 'site/involved/Volunteer')->name('volunteer');
    Route::inertia('support-our-programmes', 'site/involved/Support')->name('support');
});

Route::inertia('contact', 'site/Contact')->name('contact');
Route::inertia('donate', 'site/Donate')->name('donate');

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
});

/*
|--------------------------------------------------------------------------
| Staff Portal
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
