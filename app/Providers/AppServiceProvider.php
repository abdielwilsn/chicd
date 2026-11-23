<?php

namespace App\Providers;

use App\Models\FieldActivity;
use App\Models\OfficeAttendance;
use App\Models\Timesheet;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureStaffPortal();
        $this->configurePaystack();
    }

    /**
     * An HTTP client for the Paystack API, used for donations:
     * Http::paystack()->post('transaction/initialize', [...]).
     */
    protected function configurePaystack(): void
    {
        Http::macro('paystack', fn (): PendingRequest => Http::baseUrl(config('services.paystack.base_url'))
            ->withToken((string) config('services.paystack.secret_key'))
            ->acceptJson()
            ->asJson()
            ->connectTimeout(5)
            ->timeout(20));
    }

    /**
     * Short, stable names for polymorphic records (correction requests and
     * the audit trail), and the gate for the admin pages.
     */
    protected function configureStaffPortal(): void
    {
        Relation::enforceMorphMap([
            'office_attendance' => OfficeAttendance::class,
            'field_activity' => FieldActivity::class,
            'timesheet' => Timesheet::class,
            'user' => User::class,
        ]);

        Gate::define('access-admin', fn (User $user): bool => $user->isAdmin() && $user->isActive());
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
