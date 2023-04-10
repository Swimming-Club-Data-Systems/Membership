@component('mail::message')
# Hello {{$payment->user->name}},

We have issued a {{$amountRefunded}} refund for payment reference #{{$payment->id}}. The total amount refunded for this payment is {{$payment->formatted_amount_refunded}}.

We have applied the refund to your original payment method; {{$payment->paymentMethod->description}}.

Kind regards,<br>
The {{ tenant()->getOption("CLUB_NAME") }} Team
@endcomponent
