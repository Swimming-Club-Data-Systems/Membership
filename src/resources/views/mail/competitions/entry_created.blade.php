@component('mail::message')
# Hello {{$entry->member->user?->name}},

Your entry to {{ $entry->competition->name }} for {{$entry->member->name}} is with us.

We'll be in touch again when your entry has been processed and if there are any refunds for rejections.

Kind regards,<br>
The {{ tenant()->getOption("CLUB_NAME") }} Team
@endcomponent
