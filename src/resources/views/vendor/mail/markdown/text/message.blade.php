@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            @if (tenant())
                {{ tenant()->getOption("CLUB_NAME") }}
            @else
                {{ config('app.name') }}
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

Unwanted email? Report mail abuse via https://forms.office.com/Pages/ResponsePage.aspx?id=eUyplshmHU2mMHhet4xottqTRsfDlXxPnyldf9tMT9ZUODZRTFpFRzJWOFpQM1pLQ0hDWUlXRllJVS4u

© {{ date('Y') }} {{ config('app.name') }}. @lang('All rights reserved')
@endcomponent
@endslot
@endcomponent
