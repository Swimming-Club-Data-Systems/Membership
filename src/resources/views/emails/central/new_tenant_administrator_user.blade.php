@component('mail::message')
# Hello {{$user->first_name}},

You have been assigned as an administrator of {{$tenant->Name}} in SCDS System Administration.

To get started you will need to set your password. Please follow the link to reset your password and get started.

@component('mail::button', ['url' => $link])
    Reset password
@endcomponent

Once you have reset your password, you can log into SCDS System Administration at [{{route('central.login')}}]({{route('central.login')}}).

Thanks,<br>
Swimming Club Data Systems
@endcomponent
