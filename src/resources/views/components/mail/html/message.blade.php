<x-mail.html.layout>
    {{-- Header --}}
    @slot('header')
        <x-mail.html.header :url="config('app.url')">
            @if (tenant())
                @if (tenant()->getOption("LOGO_DIR"))
                    <img src="{{ getUploadedAssetUrl(tenant()->getOption("LOGO_DIR")) }}logo-75.png"
                         srcset="{{ getUploadedAssetUrl(tenant()->getOption("LOGO_DIR")) }}logo-75@2x.png 2x, {{ getUploadedAssetUrl(tenant()->getOption("LOGO_DIR")) }}logo-75@3x.png 3x"
                         aria-hidden="true" alt="" />
                @else
                    {{ tenant()->getOption("CLUB_NAME") }}
                @endif
            @else
                <img src="{{ asset("img/corporate/scds.svg") }}" style="height: 75px" aria-hidden="true" alt="" />
            @endif
        </x-mail.html.header>
    @endslot

    {{-- Body --}}
    {{ $slot }}

    {{-- Subcopy --}}
    @isset($subcopy)
        @slot('subcopy')
            <x-mail.html.subcopy>
                {{ $subcopy }}
            </x-mail.html.subcopy>
        @endslot
    @endisset

    {{-- Footer --}}
    @slot('footer')
        <x-mail.html.footer>
            {{ $additional_footer ?? '' }}

            @if (tenant())
                <p>Provided to {{ tenant()->getOption("CLUB_NAME") }} by SCDS.</p>

                <p>Control your email options in <a href="{{ route("my_account.email") }}">My Account</a>.</p>
            @endif

            <p>Unwanted email? <a
                    href="https://forms.office.com/Pages/ResponsePage.aspx?id=eUyplshmHU2mMHhet4xottqTRsfDlXxPnyldf9tMT9ZUODZRTFpFRzJWOFpQM1pLQ0hDWUlXRllJVS4u">Report
                    mail abuse</a>.</p>

            <p>© {{ date('Y') }} SCDS. @lang('All rights reserved.')</p>
        </x-mail.html.footer>
    @endslot
</x-mail.html.layout>
