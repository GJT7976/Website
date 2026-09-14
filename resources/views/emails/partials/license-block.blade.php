@component('mail::panel')
**{{ $license->app->name }} — {{ str_replace('_', ' ', ucfirst($license->platform_entitlement)) }} license**

License key: `{{ $license->license_key_encrypted }}`

This license can activate up to {{ $license->maximum_devices }} devices total. Keep this key in a
safe place — you'll need it to activate the app after installing it.
@endcomponent
