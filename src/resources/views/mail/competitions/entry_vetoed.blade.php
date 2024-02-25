@component('mail::message')
# Hello {{$entry->member->user?->name}},

You have vetoed {{$entry->member->name}}'s entry to {{ $entry->competition->name }}.

If you change your mind and wish to re-enter the competition, please contact your coach and ask them to select events again.

The total amount payable for this competition entry is now {{\App\Business\Helpers\Money::formatCurrency($entry->amount)}}.

Kind regards,<br>
The {{ tenant()->getOption("CLUB_NAME") }} Team
@endcomponent
