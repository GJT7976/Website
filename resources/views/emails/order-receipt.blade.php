@component('mail::message')
# Thanks for your order, {{ $order->customer_name }}!

**Order number:** {{ $order->order_number }}
**Date:** {{ $order->created_at->format('F j, Y') }}

@component('mail::table')
| Item | Amount |
| :--- | :--- |
@foreach ($order->items as $item)
| {{ $item->app_name_snapshot }}@if ($item->edition_name_snapshot) — {{ $item->edition_name_snapshot }}@endif | {{ $order->moneyLabel($item->line_subtotal_cents) }} |
@if ($item->license_label_snapshot)
| &nbsp;&nbsp;{{ $item->license_label_snapshot }} | |
@endif
@if ($item->included_platforms_snapshot)
| &nbsp;&nbsp;Includes: {{ collect($item->included_platforms_snapshot)->pluck('platform_name')->unique()->implode(', ') }} | |
@endif
@endforeach
| Subtotal | {{ $order->moneyLabel($order->subtotal_cents) }} |
@foreach ($order->taxLines as $line)
| {{ $line->tax_name_snapshot }} ({{ rtrim(rtrim($line->percentage_snapshot, '0'), '.') }}%) | {{ $order->moneyLabel($line->amount_cents) }} |
@endforeach
| **Total** | **{{ $order->moneyLabel($order->total_cents) }}** |
@endcomponent

**Payment status:** {{ ucfirst($order->payment_status) }}

@if ($order->payment_status === 'paid')
@component('mail::button', ['url' => $myDownloadsUrl])
View My Downloads
@endcomponent
@endif

@if ($licenses->isNotEmpty())
## Your license {{ $licenses->count() > 1 ? 'keys' : 'key' }}

@foreach ($licenses as $license)
@include('emails.partials.license-block', ['license' => $license])
@endforeach
@endif

If you have any questions about this order, just reply to this email or
use the Contact page on our site.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
