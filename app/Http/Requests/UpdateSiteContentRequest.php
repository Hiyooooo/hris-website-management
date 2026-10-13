<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSiteContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO: ganti dengan pengecekan login/role setelah middleware auth jadi.
        return true;
    }

    // Merapikan isian sebelum divalidasi.
    protected function prepareForValidation(): void
    {
        $gabung = [];

        if ($this->filled('primary_color')) {
            $warna = strtolower(trim($this->input('primary_color')));
            $gabung['primary_color'] = str_starts_with($warna, '#') ? $warna : '#' . $warna;
        }

        // 0812 3456 7890 atau 812... menjadi 62812...
        if ($this->filled('whatsapp_number')) {
            $nomor = preg_replace('/\D+/', '', $this->input('whatsapp_number'));
            if (str_starts_with($nomor, '0')) {
                $nomor = '62' . substr($nomor, 1);
            } elseif (str_starts_with($nomor, '8')) {
                $nomor = '62' . $nomor;
            }
            $gabung['whatsapp_number'] = $nomor;
        }

        $this->merge($gabung);
    }

    public function rules(): array
    {
        return [
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'favicon' => ['nullable', 'file', 'mimes:png,ico,webp', 'max:512'],
            'remove_logo' => ['nullable', 'boolean'],
            'remove_favicon' => ['nullable', 'boolean'],

            'primary_color' => ['required', 'string', 'regex:/^#[0-9a-f]{6}$/'],

            'hero_headline' => ['nullable', 'string', 'max:120'],
            'hero_subheadline' => ['nullable', 'string', 'max:300'],
            'cta_text' => ['nullable', 'string', 'max:40'],

            'whatsapp_number' => ['nullable', 'regex:/^62\d{8,13}$/'],
            'whatsapp_message_template' => ['nullable', 'string', 'max:300'],

            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:30'],

            'footer_text' => ['nullable', 'string', 'max:150'],

            'social_links' => ['nullable', 'array'],
            'social_links.instagram' => ['nullable', 'url', 'max:255'],
            'social_links.facebook' => ['nullable', 'url', 'max:255'],
            'social_links.linkedin' => ['nullable', 'url', 'max:255'],
            'social_links.youtube' => ['nullable', 'url', 'max:255'],

            'ga_id' => ['nullable', 'string', 'max:50'],
            'meta_pixel_id' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'primary_color.required' => 'Warna utama wajib diisi.',
            'primary_color.regex' => 'Warna harus berformat hex 6 digit, contoh #2563eb.',
            'whatsapp_number.regex' => 'Nomor WhatsApp tidak valid. Contoh: 0812 3456 7890.',
            'logo.image' => 'Logo harus berupa gambar.',
            'logo.mimes' => 'Logo harus berformat PNG, JPG, atau WebP.',
            'logo.max' => 'Ukuran logo maksimal 2 MB.',
            'favicon.mimes' => 'Favicon harus berformat PNG, ICO, atau WebP.',
            'favicon.max' => 'Ukuran favicon maksimal 512 KB.',
            'contact_email.email' => 'Format email tidak valid.',
            'social_links.*.url' => 'Tautan media sosial harus diawali https://',
        ];
    }
}
