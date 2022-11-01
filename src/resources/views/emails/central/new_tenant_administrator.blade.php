@component('mail::message')
# Hello {{$user->first_name}},

You have been assigned as an administrator of {{$tenant->Name}} in SCDS System Administration.

@component('mail::button', ['url' => route('central.home')])
    Visit SCDS
@endcomponent

Thanks,<br>
Swimming Club Data Systems
@endcomponent
