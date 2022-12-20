<x-mail.html.message>
    <p>Hello {{$recipient->name}},</p>

    {!! $email->Message !!}

    @slot('additional_footer')
        @if($recipient->unsubscribe_link)
            <p>You can unsubscribe from most Notify emails at any time. <a href="{{$recipient->unsubscribe_link}}">Unsubscribe
                    now</a>.</p>
        @endif
    @endslot
</x-mail.html.message>
