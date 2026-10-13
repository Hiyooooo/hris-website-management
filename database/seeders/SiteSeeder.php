<?php

namespace Database\Seeders;

use App\Models\Sites;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SiteSeeder extends Seeder
{
    public function run(): void
    {
        $daftar = [
            ['01-kerjaku', 'Kerjaku', 'kerjaku.test'],
            ['02-lumio', 'Lumio', 'lumio.test'],
            ['03-nexora', 'Nexora', 'nexora.test'],
        ];

        foreach ($daftar as [$siteId, $nama, $domain]) {
            // Secret hanya data uji. Cast 'encrypted' di model mengenkripsinya otomatis.
            Sites::firstOrCreate(
                ['site_id' => $siteId],
                ['name' => $nama, 'domain' => $domain, 'secret' => Str::random(40)],
            );
        }
    }
}
