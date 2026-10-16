<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('site_id')) {
            $this->merge([
                'site_id' => strtolower(trim($this->input('site_id'))),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'site_id' => ['required', 'string', 'alpha_dash', 'max:50', 'unique:sites,site_id'],
            'name' => ['required', 'string', 'max:100'],
            'domain' => ['required', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'site_id.required' => 'ID Situs wajib diisi.',
            'site_id.alpha_dash' => 'ID Situs hanya boleh berisi huruf, angka, strip (-), dan garis bawah (_).',
            'site_id.unique' => 'ID Situs ini sudah digunakan oleh website lain.',
            'name.required' => 'Nama website wajib diisi.',
            'domain.required' => 'Domain website wajib diisi.',
        ];
    }
}
