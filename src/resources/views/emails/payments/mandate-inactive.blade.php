@component('mail::message')
# Hello {{$user->Forename}},

@if($paymentMethod?->type == "bacs_debit")
Your direct debit ({{\App\Business\Helpers\PaymentMethod::formatNameFromData($paymentMethod->type, $paymentMethod->pm_type_data)}}) has been cancelled and can no longer be used for new payments.
@else
One of your recurring payment methods has been cancelled and can no longer be used for new payments.
@endif

@if($newDefaultPaymentMethod?->type == "bacs_debit")
Because you had another Direct Debit available, we have switched your default Direct Debit to {{\App\Business\Helpers\PaymentMethod::formatNameFromData($newDefaultPaymentMethod->type, $newDefaultPaymentMethod->pm_type_data)}}.
You can [change this to another Direct Debit in your club account]({{route('payments.methods.index')}}).
@elseif(!$newDefaultPaymentMethod)
You now don't have a Direct Debit available. [Please head to your club account to add a new one]({{route('payments.methods.index')}}).
@endif

If your Direct Debit has been cancelled because you are leaving the club, please ensure you pay any outstanding amount owed.
The club will tell you how to do this.

Kind regards,<br>
The {{ tenant()->getOption("CLUB_NAME") }} Team
@endcomponent
