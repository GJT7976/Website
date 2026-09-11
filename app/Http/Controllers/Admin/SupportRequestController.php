<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportRequestController extends Controller
{
    public function index(Request $request): View
    {
        $query = SupportRequest::with('app')->latest();

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        return view('admin.support.index', [
            'requests' => $query->paginate(20)->withQueryString(),
        ]);
    }

    public function show(SupportRequest $support): View
    {
        if ($support->status === 'new') {
            $support->update(['status' => 'read']);
        }

        return view('admin.support.show', ['supportRequest' => $support]);
    }

    public function update(Request $request, SupportRequest $support): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:new,read,resolved'],
        ]);

        $support->update($data);

        return back()->with('status', 'Support request updated.');
    }
}
