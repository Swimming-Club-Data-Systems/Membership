Hello {{$recipient->name}},

{!! html_entity_decode(strip_tags($email->Message)) !!}

@if($recipient->unsubscribe_link)
You can unsubscribe from most Notify emails at any time by clicking this link {{$recipient->unsubscribe_link}}
@endif

@if (tenant())
Provided to {{ tenant()->getOption("CLUB_NAME") }} by SCDS.

You can control your email options in My Account.
@endif

Unwanted email? Report mail abuse at https://forms.office.com/Pages/ResponsePage.aspx?id=eUyplshmHU2mMHhet4xottqTRsfDlXxPnyldf9tMT9ZUODZRTFpFRzJWOFpQM1pLQ0hDWUlXRllJVS4u.

© {{ date('Y') }} SCDS. @lang('All rights reserved.')
