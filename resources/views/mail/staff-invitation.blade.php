<x-mail::message>
# Welcome, {{ strtok($user->name, ' ') ?: $user->name }}

You have an account on the {{ config('chicd.short_name') }} staff portal, where you clock in and out at the office and record your field activities.

Your username is **{{ $user->email }}**. Choose your password to get started:

<x-mail::button :url="$setPasswordUrl">
Set my password
</x-mail::button>

This link works for {{ $expiresInMinutes }} minutes. If it has expired, go to the sign-in page, choose "Forgot password?" and enter your email address to get a new one.

Once you are signed in, you can turn on two-factor authentication or add a passkey under Settings to keep your account secure.

If you were not expecting this email, please let the {{ config('chicd.short_name') }} office know.

{{ config('chicd.short_name') }} staff portal
</x-mail::message>
