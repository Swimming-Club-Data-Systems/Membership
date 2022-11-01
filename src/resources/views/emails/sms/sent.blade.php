@component('mail::message')
# Hello {{$sms->author->Forename}},

We have finished sending your SMS.

The message read:<br>{{Str::finish($sms->message, '.')}}

The total fee for this message was {{$totalFee}} and this has been debited from your club's Pay As You Go balance.

@if ($sentUsers > 0)
We have sent your message to {{$sentUsers}} recipients.
@endif @if ($failedUsers > 0)
We were unable to send your message to {{$failedUsers}} recipients.
@endif

Thank you for using Notify SMS,<br>
The Swimming Club Data Systems Team
@endcomponent
