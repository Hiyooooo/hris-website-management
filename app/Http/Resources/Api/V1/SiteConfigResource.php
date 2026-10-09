<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SiteConfigResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $content = $this->content;
        $seo = $this->seo;
        $waNumber = $content->whatsapp_number ?? '';
        $waText = $content->whatsapp_message_template ?? '';
        $waUrl = $waNumber ? 'https://wa.me/' . preg_replace('/[^0-9]/', '', $waNumber) . '?text=' . urlencode($waText) : '';

        return [
            'site' => [
                'id' => $this->site_id,
                'name' => $this->name,
                'domain' => $this->domain,
            ],
            'branding' => [
                'logo_url' => $content->logo_url ?? '',
                'favicon_url' => $content->favicon_url ?? '',
                'primary_color' => $content->primary_color ?? '#0284c7',
            ],
            'hero' => [
                'headline' => $content->hero_headline ?? '',
                'subheadline' => $content->hero_subheadline ?? '',
                'cta_text' => $content->cta_text ?? '',
            ],
            'cta' => [
                'whatsapp_number' => $waNumber,
                'whatsapp_message_template' => $waText,
                'whatsapp_url' => $waUrl,
            ],
            'contact' => [
                'email' => $content->contact_email ?? '',
                'phone' => $content->contact_phone ?? '',
                'social_links' => $content->social_links ?? [],
            ],
            'footer_text' => $content->footer_text ?? '',
            'tracking' => [
                'ga_id' => $content->ga_id ?? null,
                'meta_pixel_id' => $content->meta_pixel_id ?? null,
            ],
            'seo' => [
                'meta_title' => $seo->meta_title ?? '',
                'meta_description' => $seo->meta_description ?? '',
                'keywords' => $seo->keywords ?? [],
                'og_image_url' => $seo->og_image_url ?? '',
            ],
        ];
    }
}
