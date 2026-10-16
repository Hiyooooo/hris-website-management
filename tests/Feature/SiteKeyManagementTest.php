<?php

namespace Tests\Feature;

use App\Models\Sites;
use App\Services\SiteSecretService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteKeyManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_render_keys_management_page(): void
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

        $response = $this->get(route('sites.keys.show', $site));

        $response->assertStatus(200)
            ->assertSee('Kunci Akses API: TalentaCloud')
            ->assertSee('01-talentacloud')
            ->assertSee($rawSecret)
            ->assertSee('Konfigurasi .env (Next.js)')
            ->assertSee('API_URL=')
            ->assertSee('SITE_ID=01-talentacloud')
            ->assertSee('REVALIDATE=120');
    }

    public function test_it_can_regenerate_secret_key_and_immediately_revokes_old_key(): void
    {
        $service = new SiteSecretService();
        $site = Sites::create([
            'site_id' => '01-talentacloud',
            'name' => 'TalentaCloud',
            'domain' => 'talentacloud.id',
            'is_active' => true,
            'secret' => 'temp',
        ]);
        $oldSecret = $service->setInitialSecret($site);

        // Verifikasi old secret diterima API
        $resOld = $this->withHeaders([
            'X-Site-Id' => '01-talentacloud',
            'X-Site-Secret' => $oldSecret,
        ])->getJson('/api/v1/site/config');
        $resOld->assertStatus(200);

        // Admin menekan tombol Regenerate Key di UI
        $response = $this->post(route('sites.keys.regenerate', $site));
        $response->assertRedirect(route('sites.keys.show', $site));
        $response->assertSessionHas('success');

        $site->refresh();
        $newSecret = $site->secret;

        $this->assertNotEquals($oldSecret, $newSecret);
        $this->assertStringStartsWith('sec_01-talentacloud_', $newSecret);

        // Kunci LAMA sekarang HARUS langsung ditolak (401)
        $resReject = $this->withHeaders([
            'X-Site-Id' => '01-talentacloud',
            'X-Site-Secret' => $oldSecret,
        ])->getJson('/api/v1/site/config');
        $resReject->assertStatus(401);

        // Kunci BARU harus diterima (200)
        $resAccept = $this->withHeaders([
            'X-Site-Id' => '01-talentacloud',
            'X-Site-Secret' => $newSecret,
        ])->getJson('/api/v1/site/config');
        $resAccept->assertStatus(200);
    }
}
