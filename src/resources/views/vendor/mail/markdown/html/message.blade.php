@component('mail::layout')
{{-- Header --}}
@slot('header')
@component('mail::header', ['url' => config('app.url')])
@if (tenant())
@if (tenant()->getOption("LOGO_DIR"))
<img src="{{ getUploadedAssetUrl(tenant()->getOption("LOGO_DIR")) }}logo-75.png" srcset="{{ getUploadedAssetUrl(tenant()->getOption("LOGO_DIR")) }}logo-75@2x.png 2x, {{ getUploadedAssetUrl(tenant()->getOption("LOGO_DIR")) }}logo-75@3x.png 3x" aria-hidden="true" alt="" />
@else
{{ tenant()->getOption("CLUB_NAME") }}
@endif
@else
<img src="{{ asset("img/corporate/scds.svg") }}" style="height: 75px" aria-hidden="true" alt="" />
@endif
@endcomponent
@endslot

{{-- Body --}}
{{ $slot }}

{{-- Subcopy --}}
@isset($subcopy)
@slot('subcopy')
@component('mail::subcopy')
{{ $subcopy }}
@endcomponent
@endslot
@endisset

{{-- Footer --}}
@slot('footer')
@component('mail::footer')
@if (tenant())
Provided to {{ tenant()->getOption("CLUB_NAME") }} by SCDS.

Control your email options in [My Account]({{ 'https://' . tenant()->Domain . route("my_account.email", null, false) }}).
@endif

Unwanted email? [Report mail abuse](https://forms.office.com/Pages/ResponsePage.aspx?id=eUyplshmHU2mMHhet4xottqTRsfDlXxPnyldf9tMT9ZUODZRTFpFRzJWOFpQM1pLQ0hDWUlXRllJVS4u).

© {{ date('Y') }} SCDS. @lang('All rights reserved.')
@endcomponent
@endslot
@endcomponent
