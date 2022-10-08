@component('mail::message')
# Error Report

This is an error report. The user in question was.

@component('mail::button', ['url' => ''])
Button Text
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
