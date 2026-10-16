@extends('layouts.admin')

@section('title', 'SEO & OpenGraph ' . $site->name)

@section('content')
<a class="back" href="{{ route('sites.index') }}">Kembali ke Daftar Website</a>
<h1>Pengaturan SEO & OpenGraph: {{ $site->name }}</h1>
<p class="sub">{{ $site->domain }} · ID {{ $site->site_id }}</p>

<nav class="tabs">
    <a class="tab" href="{{ route('sites.content.edit', $site) }}">Konten & Tampilan</a>
    <a class="tab active" href="{{ route('sites.seo.edit', $site) }}">SEO & OpenGraph</a>
    <a class="tab" href="{{ route('sites.keys.show', $site) }}">Kunci Akses</a>
</nav>

<div class="notice">
    <strong>Catatan:</strong> Perubahan konfigurasi tampil di situs dalam ±2 menit karena sistem cache ISR.
</div>

<div class="layout">
    <form class="form" method="POST" action="{{ route('sites.seo.update', $site) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <fieldset>
            <legend>Meta Tags Mesin Pencari (Search Engine)</legend>

            <label for="meta_title">Meta Title</label>
            <input 
                id="meta_title" 
                name="meta_title" 
                type="text" 
                maxlength="70" 
                placeholder="Contoh: {{ $site->name }} - Software HRIS & Payroll Terbaik di Indonesia" 
                value="{{ old('meta_title', $seo->meta_title) }}"
            >
            <div class="char-count"><span id="title_count">0</span>/70 karakter (optimal: 50-60)</div>
            @error('meta_title') <span class="err">{{ $message }}</span> @enderror

            <label for="meta_description">Meta Description</label>
            <textarea 
                id="meta_description" 
                name="meta_description" 
                maxlength="160" 
                placeholder="Deskripsi singkat yang muncul di hasil pencarian Google..."
            >{{ old('meta_description', $seo->meta_description) }}</textarea>
            <div class="char-count"><span id="desc_count">0</span>/160 karakter (optimal: 140-155)</div>
            @error('meta_description') <span class="err">{{ $message }}</span> @enderror

            <label for="keywords">Kata Kunci (Keywords)</label>
            <input 
                id="keywords" 
                name="keywords" 
                type="text" 
                placeholder="hris indonesia, absensi online, aplikasi payroll, software cuti" 
                value="{{ old('keywords', is_array($seo->keywords) ? implode(', ', $seo->keywords) : '') }}"
            >
            <span class="hint">Pisahkan setiap kata kunci dengan tanda koma (,).</span>
            @error('keywords') <span class="err">{{ $message }}</span> @enderror
        </fieldset>

        <fieldset>
            <legend>Banner Media Sosial (OpenGraph Image)</legend>

            <label for="og_image">Banner Gambar Media Sosial (og:image)</label>
            <input id="og_image" name="og_image" type="file" accept=".png,.jpg,.jpeg,.webp">
            <span class="hint">Disarankan ukuran 1200 x 630 pixel (rasio 1.91:1). Maksimal 2 MB.</span>
            @error('og_image') <span class="err">{{ $message }}</span> @enderror

            @if ($seo->og_image_url)
            <label class="cek">
                <input type="checkbox" name="remove_og_image" value="1"> Hapus banner saat ini
            </label>
            @endif
        </fieldset>

        <div class="aksi">
            <button class="btn" type="submit">Simpan Pengaturan SEO</button>
        </div>
    </form>

    {{-- Live Social Media Card Preview --}}
    <aside class="preview">
        <h3>Live Preview Kartu Media Sosial (Facebook / WhatsApp / LinkedIn / X)</h3>

        <div class="og-card">
            <div class="og-img-wrap" id="pv_og_wrap">
                @php
                    $currentOgUrl = $seo->og_image_url 
                        ? (str_starts_with($seo->og_image_url, 'http') ? $seo->og_image_url : asset('storage/' . $seo->og_image_url)) 
                        : null;
                @endphp
                <img 
                    id="pv_og_img" 
                    src="{{ $currentOgUrl ?? '' }}" 
                    alt="OpenGraph Preview" 
                    style="{{ $currentOgUrl ? '' : 'display: none;' }}"
                >
                <span id="pv_og_empty" class="muted" style="{{ $currentOgUrl ? 'display: none;' : '' }}">
                    [ Belum Ada Banner OG ]
                </span>
            </div>
            <div class="og-content">
                <div class="og-domain">{{ $site->domain }}</div>
                <div class="og-title" id="pv_og_title">
                    {{ $seo->meta_title ?: $site->name . ' - Software HRIS Modern' }}
                </div>
                <div class="og-desc" id="pv_og_desc">
                    {{ $seo->meta_description ?: 'Solusi kelola absensi GPS, payroll otomatis, dan evaluasi kinerja karyawan terpadu.' }}
                </div>
            </div>
        </div>

        <div style="margin-top: 20px; font-size: 13px; color: #6b7280;">
            <p><strong>Tips SEO:</strong></p>
            <ul style="padding-left: 18px; margin-top: 6px; line-height: 1.6;">
                <li>Gunakan kata kunci utama produk di awal judul.</li>
                <li>Buat deskripsi yang memicu rasa ingin tahu pembaca.</li>
                <li>Pastikan banner OG tidak buram dan relevan.</li>
            </ul>
        </div>
    </aside>
</div>
@endsection

@push('scripts')
<script>
    const inputTitle = document.getElementById('meta_title');
    const inputDesc = document.getElementById('meta_description');
    const inputOgFile = document.getElementById('og_image');

    const titleCount = document.getElementById('title_count');
    const descCount = document.getElementById('desc_count');

    const pvTitle = document.getElementById('pv_og_title');
    const pvDesc = document.getElementById('pv_og_desc');
    const pvImg = document.getElementById('pv_og_img');
    const pvEmpty = document.getElementById('pv_og_empty');

    const defaultTitle = "{{ $site->name }} - Software HRIS Modern";
    const defaultDesc = "Solusi kelola absensi GPS, payroll otomatis, dan evaluasi kinerja karyawan terpadu.";

    function updateTitle() {
        const val = inputTitle.value.trim();
        titleCount.textContent = inputTitle.value.length;
        pvTitle.textContent = val.length > 0 ? val : defaultTitle;
    }

    function updateDesc() {
        const val = inputDesc.value.trim();
        descCount.textContent = inputDesc.value.length;
        pvDesc.textContent = val.length > 0 ? val : defaultDesc;
    }

    inputTitle.addEventListener('input', updateTitle);
    inputDesc.addEventListener('input', updateDesc);
    updateTitle();
    updateDesc();

    // Live preview untuk file gambar banner yang dipilih
    inputOgFile.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                pvImg.src = event.target.result;
                pvImg.style.display = 'block';
                pvEmpty.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }
    });
</script>
@endpush
