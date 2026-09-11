@component('mail::message')
# New contact message

**From:** {{ $supportRequest->name }} ({{ $supportRequest->email }})
@if ($supportRequest->subject)
**Subject:** {{ $supportRequest->subject }}
@endif
@if ($supportRequest->app)
**App:** {{ $supportRequest->app->name }}
@endif

{{ $supportRequest->message }}

@component('mail::button', ['url' => route('admin.support.index')])
View in Admin
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
