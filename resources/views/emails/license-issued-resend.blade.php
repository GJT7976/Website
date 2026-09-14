@component('mail::message')
# Your license key

@include('emails.partials.license-block', ['license' => $license])

If you have any questions or need to move your license to a new device,
just reply to this email or use the Contact page on our site.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
