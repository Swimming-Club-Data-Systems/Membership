<x-mail::message>
Hi {{ $deletedFor->Forename }}, {{ $memberName }} has been deleted from the {{ tenant()->getOption("CLUB_NAME") }} membership system.

This action can not be undone. If you deleted the member by mistake, you will need to set them up again as a new member.

Any users that were linked to this member have not been modified.

Kind regards,<br>
The {{ tenant()->getOption("CLUB_NAME") }} Team
</x-mail::message>
