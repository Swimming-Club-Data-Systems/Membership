@component('mail::message')
# Hello {{ $user->Forename }},

Please confirm your login by entering the following code in your web browser.

@component('mail::panel')
{{ $code }}
@endcomponent

If you did not just try to log in to the {{ tenant()->getOption("CLUB_NAME") }}, please consider changing your password.

Kind regards,<br>
The {{ tenant()->getOption("CLUB_NAME") }} Team
@endcomponent
