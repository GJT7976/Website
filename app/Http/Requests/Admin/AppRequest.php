<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class AppRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $appId = $this->route('app')?->id;

        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:150', 'alpha_dash', Rule::unique('apps', 'slug')->ignore($appId)],
            'tagline' => ['nullable', 'string', 'max:200'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'long_description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:app_categories,id'],

            'version' => ['nullable', 'string', 'max:50'],
            'release_date' => ['nullable', 'date'],
            'updated_on' => ['nullable', 'date'],

            'is_free' => ['sometimes', 'boolean'],
            'price' => ['nullable', 'numeric', 'min:0', 'required_if:is_free,0'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'lte:price'],
            'currency' => ['nullable', 'string', 'size:3'],

            'google_play_url' => ['nullable', 'url', 'max:255'],
            'microsoft_store_url' => ['nullable', 'url', 'max:255'],
            'apple_url' => ['nullable', 'url', 'max:255'],

            'direct_purchase_enabled' => ['sometimes', 'boolean'],
            'stripe_product_id' => ['nullable', 'string', 'max:255'],
            'stripe_price_id' => ['nullable', 'string', 'max:255'],

            'documentation_url' => ['nullable', 'url', 'max:255'],
            'privacy_policy_url' => ['nullable', 'url', 'max:255'],
            'support_info' => ['nullable', 'string'],
            'system_requirements' => ['nullable', 'string'],

            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'is_featured' => ['sometimes', 'boolean'],
            'featured_order' => ['nullable', 'integer', 'min:0'],

            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:255'],

            'demo_enabled' => ['sometimes', 'boolean'],
            'demo_type' => ['nullable', 'string', 'max:50'],
            'demo_url' => ['nullable', 'string', 'max:255'],
            'demo_version' => ['nullable', 'string', 'max:50'],
            'demo_instructions' => ['nullable', 'string'],
            'demo_warning' => ['nullable', 'string'],
            'demo_reset_mode' => ['nullable', 'string', 'max:255'],

            'platforms' => ['nullable', 'array'],
            'platforms.*' => ['exists:platforms,id'],
        ];
    }

    /**
     * Slug falls back to a name-derived slug when left blank.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->slug ?: Str::slug($this->name),
        ]);
    }
}
