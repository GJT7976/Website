<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_email' => ['required', 'email', 'max:255'],
            'billing_company' => ['nullable', 'string', 'max:150'],
            'billing_address' => ['required', 'string', 'max:255'],
            'billing_city' => ['required', 'string', 'max:100'],
            'billing_province' => ['nullable', 'string', 'max:100'],
            'billing_postal_code' => ['required', 'string', 'max:20'],
            'billing_country' => ['required', 'string', 'size:2'],
        ];
    }
}
