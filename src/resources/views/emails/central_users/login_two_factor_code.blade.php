@component('mail::message')
# Hello {{ $user->first_name }},

Please confirm your login by entering the following code in your web browser.

@component('mail::panel')
{{ $code }}
@endcomponent

If you did not just try to log in to {{ config('app.name') }}, please consider changing your password.

Kind regards,<br>
The Swimming Club Data Systems Team
@endcomponent
