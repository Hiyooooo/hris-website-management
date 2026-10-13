@extends('layouts.admin')

@section('title', 'Konten '.$site->name)

@section('content')
<a class="back" href="{{ route('sites.index') }}">← Semua website</a>
<h1>{{ $content->exists ? 'Edit' : 'Tambah' }} konten: {{ $site->name }}</h1>
<p class="sub">{{ $site->domain }} · ID {{ $site->site_id }}</p>

<div class="layout">
    <form class="form" method="POST" action="{{ route('sites.content.update', $site) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <fieldset>
            <legend>Identitas visual</legend>

            <label for="logo">Logo</label>
            <input id="logo" name="logo" type="file" accept=".png,.jpg,.jpeg,.webp">
            <span class="hint">PNG, JPG, atau WebP. Maksimal 2 MB.</span>
            @error('logo') <span class="err">{{ $message }}</span> @enderror
            @if ($content->logo_url)
            <label class="cek"><input type="checkbox" name="remove_logo" value="1"> Hapus logo saat ini</label>
            @endif

            <label for="favicon">Favicon</label>
            <input id="favicon" name="favicon" type="file" accept=".png,.ico,.webp">
            <span class="hint">PNG, ICO, atau WebP. Maksimal 512 KB.</span>
            @error('favicon') <span class="err">{{ $message }}</span> @enderror
            @if ($content->favicon_url)
            <label class="cek"><input type="checkbox" name="remove_favicon" value="1"> Hapus favicon saat ini</label>
            @endif

            <label for="primary_color">Warna utama</label>
            <div class="warna">
                <input id="primary_color_picker" type="color" value="{{ old('primary_color', $content->primary_color ?? '#2563eb') }}" aria-label="Pilih warna">
                <input id="primary_color" name="primary_color" type="text" maxlength="7" placeholder="#2563eb" value="{{ old('primary_color', $content->primary_color ?? '#2563eb') }}">
            </div>
            @error('primary_color') <span class="err">{{ $message }}</span> @enderror
        </fieldset>

        <fieldset>
            <legend>Hero dan ajakan (CTA)</legend>

            <label for="hero_headline">Judul hero</label>
            <input id="hero_headline" name="hero_headline" type="text" maxlength="120" value="{{ old('hero_headline', $content->hero_headline) }}">
            @error('hero_headline') <span class="err">{{ $message }}</span> @enderror

            <label for="hero_subheadline">Deskripsi hero</label>
            <textarea id="hero_subheadline" name="hero_subheadline" maxlength="300">{{ old('hero_subheadline', $content->hero_subheadline) }}</textarea>
            @error('hero_subheadline') <span class="err">{{ $message }}</span> @enderror

            <label for="cta_text">Teks tombol utama</label>
            <input id="cta_text" name="cta_text" type="text" maxlength="40" value="{{ old('cta_text', $content->cta_text) }}">
            @error('cta_text') <span class="err">{{ $message }}</span> @enderror
        </fieldset>

        <fieldset>
            <legend>Kontak dan WhatsApp</legend>

            <label for="whatsapp_number">Nomor WhatsApp</label>
            <input id="whatsapp_number" name="whatsapp_number" type="text" placeholder="0812 3456 7890" value="{{ old('whatsapp_number', $content->whatsapp_number) }}">
            <span class="hint">Boleh ditulis 08..., otomatis disimpan sebagai 62...</span>
            @error('whatsapp_number') <span class="err">{{ $message }}</span> @enderror

            <label for="whatsapp_message_template">Pesan WhatsApp otomatis</label>
            <textarea id="whatsapp_message_template" name="whatsapp_message_template" maxlength="300">{{ old('whatsapp_message_template', $content->whatsapp_message_template) }}</textarea>
            @error('whatsapp_message_template') <span class="err">{{ $message }}</span> @enderror

            <div class="dua">
                <div>
                    <label for="contact_email">Email</label>
                    <input id="contact_email" name="contact_email" type="email" value="{{ old('contact_email', $content->contact_email) }}">
                    @error('contact_email') <span class="err">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="contact_phone">Telepon</label>
                    <input id="contact_phone" name="contact_phone" type="text" value="{{ old('contact_phone', $content->contact_phone) }}">
                    @error('contact_phone') <span class="err">{{ $message }}</span> @enderror
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>Footer dan media sosial</legend>

            <label for="footer_text">Teks footer</label>
            <input id="footer_text" name="footer_text" type="text" maxlength="150" value="{{ old('footer_text', $content->footer_text) }}">
            @error('footer_text') <span class="err">{{ $message }}</span> @enderror

            @foreach ($sosial as $kunci => $nama)
            <label for="sosial_{{ $kunci }}">{{ $nama }}</label>
            <input id="sosial_{{ $kunci }}" name="social_links[{{ $kunci }}]" type="url" placeholder="https://" value="{{ old('social_links.'.$kunci, $content->social_links[$kunci] ?? '') }}">
            @error('social_links.'.$kunci) <span class="err">{{ $message }}</span> @enderror
            @endforeach
        </fieldset>

        <fieldset>
            <legend>Analitik (opsional)</legend>
            <div class="dua">
                <div>
                    <label for="ga_id">Google Analytics ID</label>
                    <input id="ga_id" name="ga_id" type="text" placeholder="G-ABC123XYZ" value="{{ old('ga_id', $content->ga_id) }}">
                    @error('ga_id') <span class="err">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="meta_pixel_id">Meta Pixel ID</label>
                    <input id="meta_pixel_id" name="meta_pixel_id" type="text" value="{{ old('meta_pixel_id', $content->meta_pixel_id) }}">
                    @error('meta_pixel_id') <span class="err">{{ $message }}</span> @enderror
                </div>
            </div>
        </fieldset>

        <div class="aksi">
            <button type="submit" class="btn">Simpan konten</button>
            <a class="back" href="{{ route('sites.index') }}">Batal</a>
        </div>
    </form>

    <aside class="preview" id="preview" style="--warna: {{ old('primary_color', $content->primary_color) }}">
        <h3>Pratinjau</h3>
        <div class="pv-nav">
            @php($logoSrc = $content->logo_url ? asset('storage/'.$content->logo_url) : null)
            <img id="pv-logo" alt="Logo" @if ($logoSrc) src="{{ $logoSrc }}" @else hidden @endif>
            <span>{{ $site->name }}</span>
        </div>
        <h2 class="pv-judul" id="pv-title"></h2>
        <p class="pv-desk" id="pv-sub"></p>
        <span class="pv-cta" id="pv-cta"></span>
    </aside>
</div>
@endsection

@push('scripts')
<script>
    const $ = (id) => document.getElementById(id);
    const preview = $('preview');
    const picker = $('primary_color_picker');
    const hex = $('primary_color');
    const hexValid = (v) => /^#[0-9a-fA-F]{6}$/.test(v);

    function perbaruiPratinjau() {
        let v = hex.value.trim();
        if (v && v[0] !== '#') v = '#' + v;
        if (hexValid(v)) {
            preview.style.setProperty('--warna', v);
            picker.value = v;
        }
        $('pv-title').textContent = $('hero_headline').value || 'Judul hero tampil di sini';
        $('pv-sub').textContent = $('hero_subheadline').value || 'Deskripsi singkat hero.';
        $('pv-cta').textContent = $('cta_text').value || 'Tombol';
    }

    picker.addEventListener('input', () => {
        hex.value = picker.value;
        perbaruiPratinjau();
    });

    ['primary_color', 'hero_headline', 'hero_subheadline', 'cta_text'].forEach((id) =>
        $(id).addEventListener('input', perbaruiPratinjau)
    );

    $('logo').addEventListener('change', (e) => {
        const f = e.target.files[0];
        if (!f) return;
        $('pv-logo').src = URL.createObjectURL(f);
        $('pv-logo').hidden = false;
    });

    perbaruiPratinjau();
</script>
@endpush