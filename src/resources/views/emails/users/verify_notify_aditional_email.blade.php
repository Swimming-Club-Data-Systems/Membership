@component('mail::message')
# Hello {{ $name }},

We've received a request to add you as an additional recipient for squad update emails sent to {{ $user->name }} by the
{{ tenant()->getOption("CLUB_NAME") }} team.

If you consent to this, we need you to verify your email address by following this link;

@component('mail::button', ['url' => $url])
Verify email
@endcomponent

If you consent, emails will be sent to {{ $email }}.

If you did not expect to be added as an additional recipient, please ignore this email.
The confirmation link will expire after 24 hours.

For help, email [{{ tenant()->getOption("CLUB_EMAIL") }}](mailto:{{ tenant()->getOption("CLUB_EMAIL") }}).

Kind regards,<br>
The {{ tenant()->getOption("CLUB_NAME") }} Team
@endcomponent
