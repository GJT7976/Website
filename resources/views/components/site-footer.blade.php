@php
    $businessName = \App\Models\Setting::get('business_name', 'Niagara Inde Apps');
    $footerTagline = \App\Models\Setting::get('footer_tagline', 'Ideas. Innovation. Independent.');
    $businessEmail = \App\Models\Setting::get('email');
@endphp

<footer class="border-t border-border bg-white">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 gap-8 sm:grid-cols-3 lg:grid-cols-5">
            <div class="col-span-2 sm:col-span-3 lg:col-span-2">
                <p class="text-h3">{{ $businessName }}</p>
                <p class="text-body mt-2 max-w-sm">{{ $footerTagline }}</p>
                <p class="text-small mt-3">Independent Canadian software, based in Niagara, Ontario.</p>
            </div>

            <div>
                <p class="text-label">Apps</p>
                <ul class="mt-3 space-y-2">
                    <li><a href="{{ route('apps.index') }}" class="text-small hover:text-niagara-600">All Apps</a></li>
                    <li><a href="{{ route('demos.index') }}" class="text-small hover:text-niagara-600">Live Demos</a></li>
                    <li><a href="{{ route('pricing') }}" class="text-small hover:text-niagara-600">Pricing</a></li>
                </ul>
            </div>

            <div>
                <p class="text-label">Company</p>
                <ul class="mt-3 space-y-2">
                    <li><a href="{{ route('about') }}" class="text-small hover:text-niagara-600">About</a></li>
                    <li><a href="{{ route('support') }}" class="text-small hover:text-niagara-600">Support</a></li>
                    <li><a href="{{ route('contact') }}" class="text-small hover:text-niagara-600">Contact</a></li>
                    @if ($businessEmail)
                        <li><a href="mailto:{{ $businessEmail }}" class="text-small hover:text-niagara-600">{{ $businessEmail }}</a></li>
                    @endif
                </ul>
            </div>

            <div>
                <p class="text-label">Legal</p>
                <ul class="mt-3 space-y-2">
                    <li><a href="{{ route('privacy') }}" class="text-small hover:text-niagara-600">Privacy Policy</a></li>
                    <li><a href="{{ route('terms') }}" class="text-small hover:text-niagara-600">Terms</a></li>
                    <li><a href="{{ route('refunds') }}" class="text-small hover:text-niagara-600">Refund Policy</a></li>
                </ul>
            </div>
        </div>

        <div class="mt-10 flex flex-col items-start justify-between gap-3 border-t border-border pt-6 sm:flex-row sm:items-center">
            <p class="text-small">&copy; {{ now()->year }} {{ $businessName }}. All rights reserved.</p>
            <p class="text-small">Session cookies only. See our <a href="{{ route('privacy') }}" class="underline hover:text-niagara-600">Privacy Policy</a> for details.</p>
        </div>
    </div>
</footer>
