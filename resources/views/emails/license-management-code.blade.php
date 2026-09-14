@component('mail::message')
# Your license management code

Use this code to manage the devices activated on your **{{ $license->app->name }}**
license:

@component('mail::panel')
# {{ $code }}
@endcomponent

This code expires in {{ config('licensing.management_code_ttl_minutes') }} minutes. If you
didn't request this, you can safely ignore this email.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
