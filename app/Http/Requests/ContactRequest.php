<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['nullable', 'string', 'max:200'],
            'app_id' => ['nullable', 'exists:apps,id'],
            'message' => ['required', 'string', 'max:5000'],
            // Honeypot — a hidden field real users never fill in (spec §36).
            'website' => ['prohibited'],
        ];
    }
}
