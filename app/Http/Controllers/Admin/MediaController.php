<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MediaUploadRequest;
use App\Models\Media;
use App\Services\MediaLibrary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function index(Request $request): View
    {
        $query = Media::query()->latest();

        if ($search = $request->string('q')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('alt_text', 'like', "%{$search}%");
            });
        }

        return view('admin.media.index', [
            'media' => $query->paginate(24)->withQueryString(),
        ]);
    }

    public function store(MediaUploadRequest $request): RedirectResponse
    {
        MediaLibrary::storeUploadedFile($request->file('file'), Auth::id(), [
            'alt_text' => $request->input('alt_text'),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
        ]);

        return back()->with('status', 'Image uploaded.');
    }

    public function update(Request $request, Media $media): RedirectResponse
    {
        $data = $request->validate([
            'alt_text' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $media->update($data);

        return back()->with('status', 'Image details updated.');
    }

    public function destroy(Media $media): RedirectResponse
    {
        $inUse = DB::table('app_media')->where('media_id', $media->id)->exists();

        if ($inUse) {
            return back()->withErrors(['media' => 'This image is still attached to an app and cannot be deleted. Remove it from the app first.']);
        }

        Storage::disk($media->disk)->delete(array_filter([$media->path, $media->thumbnail_path]));
        $media->delete();

        return back()->with('status', 'Image deleted.');
    }
}
