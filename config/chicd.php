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

    'field_programmes' => [
        'Show Up Ready to Learn (SURL)',
        'Safe Housing Programme for Vulnerable Adults',
        'Organisational (not programme-specific)',
    ],

];
