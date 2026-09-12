<x-app-layout :title="'Order confirmation — Niagara Inde Apps'">
    <section class="mx-auto max-w-xl px-4 py-16 text-center sm:px-6 lg:px-8">
        @if ($order && $order->payment_status === 'paid')
            <span class="text-4xl" aria-hidden="true">✅</span>
            <h1 class="text-h1 mt-4">Thank you for your order!</h1>
            <p class="text-body mt-3">Order <strong>{{ $order->order_number }}</strong> is confirmed. A receipt has been sent to {{ $order->customer_email }}.</p>
        @else
            <span class="text-4xl" aria-hidden="true">⏳</span>
            <h1 class="text-h1 mt-4">Confirming your payment&hellip;</h1>
            <p class="text-body mt-3">
                @if ($order)
                    Order <strong>{{ $order->order_number }}</strong> is being confirmed with our payment processor. This page doesn't need to stay open — you'll get a receipt by email at {{ $order->customer_email }} once it's done.
                @else
                    We're finishing up — if this persists, please contact us with your payment confirmation from Stripe.
                @endif
            </p>
        @endif

        <div class="mt-8 flex flex-wrap justify-center gap-3">
            @if ($myDownloadsUrl)
                <a href="{{ $myDownloadsUrl }}" class="btn btn-primary">Go to My Downloads</a>
            @endif
            <a href="{{ route('apps.show', $app) }}" class="btn btn-secondary">Back to {{ $app->name }}</a>
            <a href="{{ route('home') }}" class="btn {{ $myDownloadsUrl ? 'btn-secondary' : 'btn-primary' }}">Return Home</a>
        </div>
    </section>
</x-app-layout>
