@component('mail::message')
# Error Report

This is an error report submitted by a user.

The user in question was {{ $user['name'] }} and they can be contacted via [{{ $user['email'] }}](mailto:{{ $user['email'] }}).

@if($userId)
@component('mail::button', ['url' => route('central.users.show', $userId)])
View user
@endcomponent
@endif

@if ($tenant)
The user was in tenant {{ $tenant['id'] }}, {{ $tenant['name'] }}.
@endif

The page url is [{{ $url }}]({{ $url }}).

The user said:

@component('mail::panel')
{{ $description }}.
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
