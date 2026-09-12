<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Page;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageContentController extends Controller
{
    public function index(): View
    {
        return view('admin.content.index', [
            'pages' => Page::orderBy('title')->get(),
        ]);
    }

    public function edit(Page $page): View
    {
        $page->load('sections');

        return view('admin.content.edit', [
            'page' => $page,
            'mediaLibrary' => Media::latest()->limit(60)->get(),
        ]);
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'published' => ['sometimes', 'boolean'],
            'sections' => ['array'],
            'sections.*.id' => ['nullable', 'exists:page_sections,id'],
            'sections.*.heading' => ['nullable', 'string', 'max:255'],
            'sections.*.body' => ['nullable', 'string'],
            'sections.*.media_id' => ['nullable', 'exists:media,id'],
            'sections.*.sort_order' => ['nullable', 'integer'],
        ]);

        $before = $page->only(['title', 'meta_title', 'meta_description', 'published']);

        $page->update([
            'title' => $data['title'],
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'published' => $request->boolean('published'),
        ]);

        AuditLogger::record('content.updated', $page, $before, $page->only(['title', 'meta_title', 'meta_description', 'published']));

        foreach ($data['sections'] ?? [] as $index => $section) {
            $page->sections()->updateOrCreate(
                ['id' => $section['id'] ?? null],
                [
                    'heading' => $section['heading'] ?? null,
                    'body' => $section['body'] ?? null,
                    'media_id' => $section['media_id'] ?? null,
                    'sort_order' => $section['sort_order'] ?? $index,
                ]
            );
        }

        return redirect()->route('admin.content.edit', $page)->with('status', 'Page updated.');
    }
}
