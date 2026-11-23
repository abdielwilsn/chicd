<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Organisation Details
    |--------------------------------------------------------------------------
    |
    | Public contact details shown across the website (header, footer and
    | contact page). Leave a value empty to hide it on the website.
    |
    */

    'name' => 'Centre for Health Innovation and Community Development',

    'short_name' => 'CHICD',

    'tagline' => 'Innovating for Health, Empowering for Life.',

    'contact' => [
        'email' => env('CHICD_CONTACT_EMAIL', 'info@chicd.org'),
        'phone' => env('CHICD_CONTACT_PHONE'),
        'address' => env('CHICD_CONTACT_ADDRESS', 'Uyo, Akwa Ibom State, Nigeria'),
    ],

    'social' => [
        'facebook' => env('CHICD_SOCIAL_FACEBOOK'),
        'instagram' => env('CHICD_SOCIAL_INSTAGRAM'),
        'linkedin' => env('CHICD_SOCIAL_LINKEDIN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | The inbox that receives website enquiries, volunteer applications and
    | confidential Safe Housing enquiries.
    |
    */

    'enquiries_email' => env('CHICD_ENQUIRIES_EMAIL', env('CHICD_CONTACT_EMAIL', 'info@chicd.org')),

    'donations_email' => env('CHICD_DONATIONS_EMAIL', env('CHICD_ENQUIRIES_EMAIL', env('CHICD_CONTACT_EMAIL', 'info@chicd.org'))),

    /*
    |--------------------------------------------------------------------------
    | Donations
    |--------------------------------------------------------------------------
    |
    | Donations are taken through Paystack (see config/services.php). A donor
    | gives to CHICD generally or to one of these funds; published programmes
    | added by an admin are offered after them, keyed by their page slug.
    |
    | Amounts are whole naira.
    |
    */

    'donation_funds' => [
        'general' => 'Donate to CHICD (where it is needed most)',
        'surl' => 'Show Up Ready to Learn (SURL)',
        'safe-housing' => 'Safe Housing Programme',
    ],

    'donation_amounts' => [
        'suggested' => [5000, 10000, 25000, 50000],
        'minimum' => 100,
        'maximum' => 50000000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Staff Portal
    |--------------------------------------------------------------------------
    |
    | Times are stored in UTC. The organisation timezone decides where a
    | working day and a month begin and end for attendance and timesheets.
    |
    | Field activities are recorded against one of these programmes. The list
    | will move to the database when programmes become admin-managed.
    |
    */

    'timezone' => env('CHICD_TIMEZONE', 'Africa/Lagos'),

    /*
    | Working days (ISO day numbers, 1 = Monday) and the start of the working
    | day. A working day with no office or field record, no approved leave and
    | no public holiday counts as an absence. Starting work after the start
    | time plus the grace period counts as late.
    */

    'working_hours' => [
        'days' => [1, 2, 3, 4, 5],
        'start' => env('CHICD_WORKDAY_START', '08:00'),
        'grace_minutes' => (int) env('CHICD_LATENESS_GRACE_MINUTES', 15),
    ],

    'field_programmes' => [
        'Show Up Ready to Learn (SURL)',
        'Safe Housing Programme for Vulnerable Adults',
        'Organisational (not programme-specific)',
    ],

];
