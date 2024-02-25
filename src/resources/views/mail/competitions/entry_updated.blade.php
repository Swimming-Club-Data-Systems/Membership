@component('mail::message')
# Hello {{$entry->member->user?->name}},

Here are the details if your entry to {{ $entry->competition->name }} for {{$entry->member->name}}.

@if($entry->competitionEventEntries->isNotEmpty())
<x-mail::table>
| Event       | Session         | Price  |
| :---------- |:--------------- | ------:|
@foreach($entry->competitionEventEntries as $eventEntry)
| {{$eventEntry->competitionEvent->name}} | {{$eventEntry->competitionEvent->session->name}} | {{\App\Business\Helpers\Money::formatCurrency($eventEntry->amount)}} |
@endforeach
</x-mail::table>

The total amount payable for this competition entry is {{\App\Business\Helpers\Money::formatCurrency($entry->amount)}}.

We'll be in touch again when your entry has been processed and if there are any refunds for rejections.

@else
This competition entry has no events.
@endif

Kind regards,<br>
The {{ tenant()->getOption("CLUB_NAME") }} Team
@endcomponent
