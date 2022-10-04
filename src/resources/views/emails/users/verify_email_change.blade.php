@component('mail::message')
# Hello {{ $user->Forename }},

We've received a request to update the email address associated with your account to {{ $email }}.

We need you to verify your email address by following this link;

@component('mail::button', ['url' => $url])
Verify email
@endcomponent

You will need to use your previous email address ({{ $user->EmailAddress }}) to sign in.

If you did not make a change to your email address, please ignore this email and consider resetting your password. The link will expire after 24 hours.

For help, email [{{ tenant()->getOption("CLUB_EMAIL") }}](mailto:{{ tenant()->getOption("CLUB_EMAIL") }}).

Kind regards,<br>
The {{ tenant()->getOption("CLUB_NAME") }} Team
@endcomponent
