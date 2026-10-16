<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSeoSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('keywords')) {
            $raw = $this->input('keywords');
            if (is_string($raw)) {
                $array = array_values(array_filter(array_map('trim', explode(',', $raw))));
                $this->merge(['keywords' => $array]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'meta_title' => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:160'],
            'keywords' => ['nullable', 'array'],
            'keywords.*' => ['string', 'max:50'],
            'og_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'remove_og_image' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'meta_title.max' => 'Meta Title maksimal 70 karakter (disarankan 50-60 karakter untuk SEO optimal).',
            'meta_description.max' => 'Meta Description maksimal 160 karakter (disarankan 150-160 karakter).',
            'og_image.image' => 'Banner OpenGraph harus berupa file gambar.',
            'og_image.mimes' => 'Format gambar banner harus PNG, JPG, JPEG, atau WebP.',
            'og_image.max' => 'Ukuran banner OpenGraph maksimal 2 MB.',
        ];
    }
}
