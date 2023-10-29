@component('mail::message')
# Hello {{$payment->name}},

We have issued a {{$amountRefunded}} refund for payment reference #{{$payment->id}}. The total amount refunded for this payment is {{$payment->formatted_amount_refunded}}.

<x-mail::table>
| Item       | Quantity         | Unit Price | Total Price  | Total Amount Refunded
| ------------- |:-------------:| --------:| --------:| --------:|
@foreach($payment->lines as $line)
| {{$line->description}} | {{$line->quantity}} | {{$line->formatted_unit_amount}} | {{$line->formatted_amount_total}} | {{$line->formatted_amount_refunded}}
@endforeach
</x-mail::table>

We have applied the refund to your original payment method; {{$payment->paymentMethod->description}}.

Kind regards,<br>
The {{ tenant()->getOption("CLUB_NAME") }} Team
@endcomponent
