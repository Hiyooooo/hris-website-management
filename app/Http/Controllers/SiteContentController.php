<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSiteRequest;
use App\Http\Requests\UpdateSiteContentRequest;
use App\Models\SiteContent;
use App\Models\Sites;
use App\Services\SiteSecretService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SiteContentController extends Controller
{
    private const WARNA_BAWAAN = '#2563eb';

    private const SOSIAL = [
        'instagram' => 'Instagram',
        'facebook' => 'Facebook',
        'linkedin' => 'LinkedIn',
        'youtube' => 'YouTube',
    ];

    public function index(): View
    {
        $sites = Sites::with(['content', 'seo'])->orderBy('name')->get();

        return view('sites.index', compact('sites'));
    }

    public function create(): View
    {
        return view('sites.create');
    }

    public function store(StoreSiteRequest $request, SiteSecretService $service): RedirectResponse
    {
        $site = Sites::create([
            'site_id' => $request->input('site_id'),
            'name' => $request->input('name'),
            'domain' => $request->input('domain'),
            'is_active' => $request->boolean('is_active', true),
            'secret' => 'temp',
        ]);

        $service->setInitialSecret($site);

        return redirect()
            ->route('sites.content.edit', $site)
            ->with('success', 'Website ' . $site->name . ' berhasil ditambahkan. Silakan lengkapi kontennya.');
    }

    // Satu halaman untuk "tambah" (konten belum ada) dan "edit" (sudah ada).
    public function edit(Sites $site): View
    {
        $content = $site->content ?? new SiteContent([
            'site_id' => $site->id,
            'primary_color' => self::WARNA_BAWAAN,
        ]);

        return view('sites.content', [
            'site' => $site,
            'content' => $content,
            'sosial' => self::SOSIAL,
        ]);
    }

    public function update(UpdateSiteContentRequest $request, Sites $site): RedirectResponse
    {
        $content = $site->content; // bisa null kalau belum pernah diisi

        $siteData = $request->safe()->only(['name', 'domain']);
        if (!empty($siteData)) {
            $site->update($siteData);
        }

        $data = $request->safe()->except([
            'name',
            'domain',
            'logo',
            'favicon',
            'remove_logo',
            'remove_favicon',
            'social_links',
        ]);
        $data['social_links'] = array_filter($request->input('social_links', [])) ?: null;

        foreach (['logo' => 'logo_url', 'favicon' => 'favicon_url'] as $input => $kolom) {
            if ($request->hasFile($input)) {
                $this->hapusFile($content?->{$kolom});
                $data[$kolom] = $request->file($input)->store("sites/{$site->site_id}", 'public');
            } elseif ($request->boolean("remove_{$input}")) {
                $this->hapusFile($content?->{$kolom});
                $data[$kolom] = null;
            }
        }

        // Satu website selalu satu baris konten. Lewat model, jadi cache API ikut dibersihkan.
        SiteContent::updateOrCreate(['site_id' => $site->id], $data);

        return redirect()
            ->route('sites.content.edit', $site)
            ->with('success', 'Konten ' . $site->name . ' berhasil disimpan.');
    }

    private function hapusFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
