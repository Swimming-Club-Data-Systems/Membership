<x-mail::message>
Hi {{ $deletedFor->Forename }}, we were unable to delete {{ $member->name }} from the {{ tenant()->getOption("CLUB_NAME") }} membership system.

The error has been recorded. Please try again later. If the issue persists, please contact SCDS Support who will investigate the issue.

Kind regards,<br>
The {{ tenant()->getOption("CLUB_NAME") }} Team
</x-mail::message>
