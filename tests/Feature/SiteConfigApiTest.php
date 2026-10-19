<?php

namespace Tests\Feature;

use App\Models\SeoSetting;
use App\Models\SiteContent;
use App\Models\Sites;
use App\Services\SiteSecretService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SiteConfigApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_401_when_headers_are_missing(): void
    {
        $response = $this->getJson('/api/v1/site/config');

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthorized: Missing credentials']);
    }

    public function test_it_returns_401_when_secret_is_invalid(): void
    {
        $site = Sites::create([
            'site_id' => '01-talentacloud',
            'name' => 'TalentaCloud',
            'domain' => 'talentacloud.id',
            'is_active' => true,
            'secret' => 'valid-secret-key',
        ]);

        $response = $this->withHeaders([
            'X-Site-Id' => '01-talentacloud',
            'X-Site-Secret' => 'wrong-secret-key',
        ])->getJson('/api/v1/site/config');

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthorized: Missing credentials']);
    }

    public function test_it_returns_401_when_site_is_inactive(): void
    {
        $site = Sites::create([
            'site_id' => '01-talentacloud',
            'name' => 'TalentaCloud',
            'domain' => 'talentacloud.id',
            'is_active' => false,
            'secret' => 'valid-secret-key',
        ]);

        $response = $this->withHeaders([
            'X-Site-Id' => '01-talentacloud',
            'X-Site-Secret' => 'valid-secret-key',
        ])->getJson('/api/v1/site/config');

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthorized: Missing credentials']);
    }

    public function test_it_returns_200_and_matches_gemini_config_contract(): void
    {
        $service = new SiteSecretService();
        $site = Sites::create([
            'site_id' => '01-talentacloud',
            'name' => 'TalentaCloud',
            'domain' => 'talentacloud.id',
            'is_active' => true,
            'secret' => 'temp',
        ]);
        $rawSecret = $service->setInitialSecret($site);

        SiteContent::create([
            'site_id' => $site->id,
            'logo_url' => 'https://example.com/logo.png',
            'favicon_url' => 'https://example.com/favicon.ico',
            'primary_color' => '#0284c7',
            'hero_headline' => 'Solusi HR Terbaik',
            'hero_subheadline' => 'Kelola absensi & payroll dengan mudah',
            'cta_text' => 'Konsultasi Sekarang',
            'whatsapp_number' => '628123456789',
            'whatsapp_message_template' => 'Halo, saya tertarik dengan demo HRIS.',
            'footer_text' => 'Hak Cipta 2026',
            'contact_email' => 'sales@talentacloud.id',
            'contact_phone' => '021-123456',
            'social_links' => ['linkedin' => 'https://linkedin.com'],
            'ga_id' => 'G-ABC123XYZ',
            'meta_pixel_id' => '1234567890',
        ]);

        SeoSetting::create([
            'site_id' => $site->id,
            'meta_title' => 'TalentaCloud - HR & Payroll Terpercaya',
            'meta_description' => 'Aplikasi HRIS terbaik di Indonesia',
            'keywords' => ['hris', 'payroll', 'absensi'],
            'og_image_url' => 'https://example.com/og.png',
        ]);

        $response = $this->withHeaders([
            'X-Site-Id' => '01-talentacloud',
            'X-Site-Secret' => $rawSecret,
        ])->getJson('/api/v1/site/config');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'site' => ['id', 'name', 'domain'],
                'branding' => ['logo_url', 'favicon_url', 'primary_color'],
                'hero' => ['headline', 'subheadline', 'cta_text'],
                'cta' => ['whatsapp_number', 'whatsapp_message_template', 'whatsapp_url'],
                'contact' => ['email', 'phone', 'social_links'],
                'footer_text',
                'tracking' => ['ga_id', 'meta_pixel_id'],
                'seo' => ['meta_title', 'meta_description', 'keywords', 'og_image_url'],
            ]);

        // Verifikasi tidak ada secret yang bocor
        $response->assertJsonMissing(['secret' => $rawSecret]);
        $response->assertJsonMissing(['deleted_at' => null]);

        // Verifikasi nilai spesifik
        $this->assertEquals('https://wa.me/628123456789?text=Halo%2C+saya+tertarik+dengan+demo+HRIS.', $response->json('cta.whatsapp_url'));
        $this->assertEquals('#0284c7', $response->json('branding.primary_color'));
    }

    public function test_it_automatically_clears_cache_when_content_or_seo_is_updated(): void
    {
        $service = new SiteSecretService();
        $site = Sites::create([
            'site_id' => '01-talentacloud',
            'name' => 'TalentaCloud',
            'domain' => 'talentacloud.id',
            'is_active' => true,
            'secret' => 'temp',
        ]);
        $rawSecret = $service->setInitialSecret($site);

        $content = SiteContent::create([
            'site_id' => $site->id,
            'hero_headline' => 'Headline Versi Lama',
        ]);

        // Request 1: Mengisi Cache
        $response1 = $this->withHeaders([
            'X-Site-Id' => '01-talentacloud',
            'X-Site-Secret' => $rawSecret,
        ])->getJson('/api/v1/site/config');

        $this->assertEquals('Headline Versi Lama', $response1->json('hero.headline'));
        $this->assertTrue(Cache::has("site:{$site->site_id}:config"));

        // Admin Mengupdate Konten
        $content->update([
            'hero_headline' => 'Headline Versi Baru dari Admin',
        ]);

        // Verifikasi Cache Langsung Terhapus Otomatis
        $this->assertFalse(Cache::has("site:{$site->site_id}:config"));

        // Request 2: Harus langsung mengembalikan data terbaru
        $response2 = $this->withHeaders([
            'X-Site-Id' => '01-talentacloud',
            'X-Site-Secret' => $rawSecret,
        ])->getJson('/api/v1/site/config');

        $this->assertEquals('Headline Versi Baru dari Admin', $response2->json('hero.headline'));
    }

    public function test_it_can_update_site_name_and_domain_from_content_form(): void
    {
        $site = Sites::create([
            'site_id' => '01-talentacloud',
            'name' => 'Nama Lama',
            'domain' => 'lama.id',
            'is_active' => true,
            'secret' => 'temp',
        ]);

        $response = $this->put(route('sites.content.update', $site), [
            'name' => 'Nama Baru Talenta',
            'domain' => 'https://baru.talentacloud.id/',
            'primary_color' => '#10b981',
            'hero_headline' => 'Headline Baru',
        ]);

        $response->assertRedirect(route('sites.content.edit', $site));
        $response->assertSessionHas('success');

        $site->refresh();
        $this->assertEquals('Nama Baru Talenta', $site->name);
        $this->assertEquals('baru.talentacloud.id', $site->domain);
    }
}
