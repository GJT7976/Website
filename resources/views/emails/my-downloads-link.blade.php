@component('mail::message')
# Your downloads

Here's your link to view and download everything you've purchased from
{{ config('app.name') }}:

@component('mail::button', ['url' => $url])
View My Downloads
@endcomponent

This link is yours to keep — reinstall or re-download anytime.

If you didn't request this, you can safely ignore this email.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
