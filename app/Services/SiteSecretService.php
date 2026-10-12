<?php

namespace App\Services;

use App\Models\Sites;
use Illuminate\Support\Str;

class SiteSecretService
{

    public function generateSecretString(string $slug): string
    {
        return 'sec_' . Str::slug($slug) . '_' . Str::random(48);
    }

    public function setInitialSecret(Sites $site): string
    {
        $newSecret = $this->generateSecretString($site->site_id);
        $site->secret = $newSecret;
        $site->save();

        return $newSecret;
    }

    public function rotateSecret(Sites $site): string
    {
        $newSecret = $this->generateSecretString($site->site_id);
        $site->secret = $newSecret;
        $site->save();

        return $newSecret;
    }
}