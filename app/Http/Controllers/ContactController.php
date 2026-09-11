<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageReceived;
use App\Http\Requests\ContactRequest;
use App\Models\App;
use App\Models\Setting;
use App\Models\SupportRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function create(): View
    {
        return view('contact', [
            'apps' => App::published()->orderBy('name')->get(),
        ]);
    }

    public function store(ContactRequest $request): RedirectResponse
    {
        $supportRequest = SupportRequest::create([
            ...$request->safe()->except(['website']),
            'ip_address' => $request->ip(),
            'status' => 'new',
        ]);

        $businessEmail = Setting::get('email');

        if ($businessEmail) {
            try {
                Mail::to($businessEmail)->send(new ContactMessageReceived($supportRequest));
            } catch (\Throwable $e) {
                // Never fail the visitor's submission because outbound mail
                // isn't configured yet — the message is already saved and
                // visible in Admin → Support.
                Log::warning('Contact notification email failed to send.', ['error' => $e->getMessage()]);
            }
        }

        return back()->with('status', "Thanks — we've received your message and will get back to you soon.");
    }
}
