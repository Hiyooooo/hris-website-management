<?php

namespace Tests\Feature;

use App\Models\SeoSetting;
use App\Models\Sites;
use App\Services\SiteSecretService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiteSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_render_seo_edit_page(): void
    {
        $site = Sites::create([
            'site_id' => '01-talentacloud',
            'name' => 'TalentaCloud',
            'domain' => 'talentacloud.id',
            'is_active' => true,
            'secret' => 'secret123',
        ]);

        $response = $this->get(route('sites.seo.edit', $site));

        $response->assertStatus(200)
            ->assertSee('Pengaturan SEO', false)
            ->assertSee('Meta Title')
            ->assertSee('Live Preview Kartu Media Sosial');
    }

    public function test_it_can_update_seo_settings_and_upload_og_image(): void
    {
        Storage::fake('public');

        $site = Sites::create([
            'site_id' => '01-talentacloud',
            'name' => 'TalentaCloud',
            'domain' => 'talentacloud.id',
            'is_active' => true,
            'secret' => 'secret123',
        ]);

        $file = UploadedFile::fake()->image('banner-og.png', 1200, 630);

        $response = $this->put(route('sites.seo.update', $site), [
            'meta_title' => 'TalentaCloud - Solusi HR Terintegrasi',
            'meta_description' => 'Aplikasi absensi dan payroll karyawan otomatis untuk perusahaan Indonesia.',
            'keywords' => 'hris, payroll, absensi online, pph 21',
            'og_image' => $file,
        ]);

        $response->assertRedirect(route('sites.seo.edit', $site));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('seo_settings', [
            'site_id' => $site->id,
            'meta_title' => 'TalentaCloud - Solusi HR Terintegrasi',
            'meta_description' => 'Aplikasi absensi dan payroll karyawan otomatis untuk perusahaan Indonesia.',
        ]);

        $seo = SeoSetting::where('site_id', $site->id)->first();
        $this->assertEquals(['hris', 'payroll', 'absensi online', 'pph 21'], $seo->keywords);
        $this->assertNotNull($seo->og_image_url);

        Storage::disk('public')->assertExists($seo->og_image_url);
    }

    public function test_it_clears_api_cache_and_updates_api_v1_config_output(): void
    {
        Storage::fake('public');

        $service = new SiteSecretService();
        $site = Sites::create([
            'site_id' => '01-talentacloud',
            'name' => 'TalentaCloud',
            'domain' => 'talentacloud.id',
            'is_active' => true,
            'secret' => 'temp',
        ]);
        $rawSecret = $service->setInitialSecret($site);

        SeoSetting::create([
            'site_id' => $site->id,
            'meta_title' => 'Judul Lama',
            'meta_description' => 'Deskripsi Lama',
            'keywords' => ['lama'],
        ]);

        // Request 1: cache terisi
        $res1 = $this->withHeaders([
            'X-Site-Id' => '01-talentacloud',
            'X-Site-Secret' => $rawSecret,
        ])->getJson('/api/v1/site/config');

        $this->assertEquals('Judul Lama', $res1->json('seo.meta_title'));
        $this->assertTrue(Cache::has("site:{$site->site_id}:config"));

        // Update SEO via Admin Form
        $this->put(route('sites.seo.update', $site), [
            'meta_title' => 'Judul SEO Baru 2026',
            'meta_description' => 'Deskripsi SEO Baru 2026',
            'keywords' => 'baru, terupdate',
        ]);

        // Cache harus otomatis bersih
        $this->assertFalse(Cache::has("site:{$site->site_id}:config"));

        // Request 2: API harus langsung mencerminkan data SEO baru
        $res2 = $this->withHeaders([
            'X-Site-Id' => '01-talentacloud',
            'X-Site-Secret' => $rawSecret,
        ])->getJson('/api/v1/site/config');

        $this->assertEquals('Judul SEO Baru 2026', $res2->json('seo.meta_title'));
        $this->assertEquals(['baru', 'terupdate'], $res2->json('seo.keywords'));
    }
}
