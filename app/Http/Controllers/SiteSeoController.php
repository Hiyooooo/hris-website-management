<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSeoSettingRequest;
use App\Models\SeoSetting;
use App\Models\Sites;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SiteSeoController extends Controller
{
    public function edit(Sites $site): View
    {
        $seo = $site->seo ?? new SeoSetting([
            'site_id' => $site->id,
            'keywords' => [],
        ]);

        return view('sites.seo', [
            'site' => $site,
            'seo' => $seo,
        ]);
    }

    public function update(UpdateSeoSettingRequest $request, Sites $site): RedirectResponse
    {
        $seo = $site->seo;

        $data = $request->safe()->except([
            'og_image',
            'remove_og_image',
        ]);

        if ($request->hasFile('og_image')) {
            $this->hapusFile($seo?->og_image_url);
            $data['og_image_url'] = $request->file('og_image')->store("sites/{$site->site_id}/og", 'public');
        } elseif ($request->boolean('remove_og_image')) {
            $this->hapusFile($seo?->og_image_url);
            $data['og_image_url'] = null;
        }

        // Simpan SEO (otomatis membersihkan cache API via model event SeoSetting::saved)
        SeoSetting::updateOrCreate(['site_id' => $site->id], $data);

        return redirect()
            ->route('sites.seo.edit', $site)
            ->with('success', 'Pengaturan SEO dan OpenGraph ' . $site->name . ' berhasil disimpan.');
    }

    private function hapusFile(?string $path): void
    {
        if ($path && !str_starts_with($path, 'http')) {
            Storage::disk('public')->delete($path);
        }
    }
}
