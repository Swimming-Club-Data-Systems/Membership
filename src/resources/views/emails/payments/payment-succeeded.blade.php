@component('mail::message')
# Hello {{$payment->name}},

Your payment to {{ tenant()->getOption("CLUB_NAME") }} has been successful. This is your payment receipt.

You have paid for the following items;

<x-mail::table>
| Item       | Quantity         | Unit Price | Total Price  |
| ------------- |:-------------:| --------:| --------:|
@foreach($payment->lines as $line)
| {{$line->description}} | {{$line->quantity}} | {{$line->formatted_unit_amount}} | {{$line->formatted_amount_total}} |
@endforeach
</x-mail::table>

Payment method details;

{{$payment->paymentMethod->description}}@if($payment->paymentMethod->information_line)<br>
{{$payment->paymentMethod->information_line}}
@endif

Kind regards,<br>
The {{ tenant()->getOption("CLUB_NAME") }} Team
@endcomponent
