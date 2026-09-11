@component('mail::message')
# Thanks for your order, {{ $order->customer_name }}!

**Order number:** {{ $order->order_number }}
**Date:** {{ $order->created_at->format('F j, Y') }}

@component('mail::table')
| Item | Amount |
| :--- | :--- |
@foreach ($order->items as $item)
| {{ $item->app_name_snapshot }} | {{ $order->moneyLabel($item->line_subtotal_cents) }} |
@endforeach
| Subtotal | {{ $order->moneyLabel($order->subtotal_cents) }} |
@foreach ($order->taxLines as $line)
| {{ $line->tax_name_snapshot }} ({{ rtrim(rtrim($line->percentage_snapshot, '0'), '.') }}%) | {{ $order->moneyLabel($line->amount_cents) }} |
@endforeach
| **Total** | **{{ $order->moneyLabel($order->total_cents) }}** |
@endcomponent

**Payment status:** {{ ucfirst($order->payment_status) }}

If you have any questions about this order, just reply to this email or
use the Contact page on our site.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
