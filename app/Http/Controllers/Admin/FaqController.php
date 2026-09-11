<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function index(): View
    {
        return view('admin.faqs.index', [
            'faqs' => Faq::with('app')->orderBy('sort_order')->paginate(30),
        ]);
    }

    public function create(): View
    {
        return view('admin.faqs.form', [
            'faq' => new Faq,
            'apps' => App::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Faq::create($this->validated($request));

        return redirect()->route('admin.faqs.index')->with('status', 'FAQ created.');
    }

    public function edit(Faq $faq): View
    {
        return view('admin.faqs.form', [
            'faq' => $faq,
            'apps' => App::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $faq->update($this->validated($request));

        return redirect()->route('admin.faqs.index')->with('status', 'FAQ updated.');
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return back()->with('status', 'FAQ deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'app_id' => ['nullable', 'exists:apps,id'],
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer'],
            'published' => ['sometimes', 'boolean'],
        ]);

        $data['published'] = $request->boolean('published');

        return $data;
    }
}
