<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class MediaUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // MIME sniffed from file contents (not just extension), plus an
            // explicit extension allow-list and size cap — spec §17. SVG is
            // deliberately excluded: it can embed executable script and
            // this library serves uploads directly, unsanitized.
            'file' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,gif,webp',
                'max:8192', // KB (8 MB)
            ],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
