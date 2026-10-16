<?php

namespace App\Http\Controllers;

use App\Models\Sites;
use App\Services\SiteSecretService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SiteKeyController extends Controller
{
    public function show(Sites $site, SiteSecretService $service): View
    {
        // Jika karena alasan tertentu situs belum punya secret, buatkan otomatis
        if (empty($site->secret)) {
            $service->setInitialSecret($site);
        }

        return view('sites.keys', [
            'site' => $site,
            'plainSecret' => $site->secret,
        ]);
    }

    public function regenerate(Sites $site, SiteSecretService $service): RedirectResponse
    {
        $newSecret = $service->rotateSecret($site);

        return redirect()
            ->route('sites.keys.show', $site)
            ->with('success', "Secret Key baru untuk {$site->name} berhasil dibuat! Segera perbarui file .env di website Next.js.");
    }
}
