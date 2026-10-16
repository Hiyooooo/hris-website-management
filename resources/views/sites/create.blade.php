@extends('layouts.admin')

@section('title', 'Tambah Website Baru')

@section('content')
<a class="back" href="{{ route('sites.index') }}">Kembali ke Daftar Website</a>
<h1>Tambah Website Baru</h1>
<p class="sub">Daftarkan website landing page baru untuk dikelola konten dan konfigurasinya.</p>

<div style="max-width: 600px; margin-top: 20px;">
    <form class="form" method="POST" action="{{ route('sites.store') }}">
        @csrf

        <fieldset>
            <legend>Informasi Website</legend>

            <label for="site_id">ID Situs (Site ID)</label>
            <input 
                id="site_id" 
                name="site_id" 
                type="text" 
                maxlength="50" 
                placeholder="contoh: 01-talentacloud" 
                value="{{ old('site_id') }}"
                required
            >
            <span class="hint">ID unik untuk komunikasi API Next.js. Gunakan huruf kecil, angka, dan tanda hubung (-).</span>
            @error('site_id') <span class="err">{{ $message }}</span> @enderror

            <label for="name">Nama Website</label>
            <input 
                id="name" 
                name="name" 
                type="text" 
                maxlength="100" 
                placeholder="contoh: TalentaCloud" 
                value="{{ old('name') }}"
                required
            >
            <span class="hint">Nama merek atau persona produk HRIS website ini.</span>
            @error('name') <span class="err">{{ $message }}</span> @enderror

            <label for="domain">Domain Website</label>
            <input 
                id="domain" 
                name="domain" 
                type="text" 
                maxlength="100" 
                placeholder="contoh: talentacloud.id" 
                value="{{ old('domain') }}"
                required
            >
            <span class="hint">Domain tujuan website tanpa awalan protokol.</span>
            @error('domain') <span class="err">{{ $message }}</span> @enderror

            <div style="margin-top: 16px;">
                <label class="cek">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') === '1' ? 'checked' : '' }}>
                    Aktifkan website ini (izinkan akses API)
                </label>
            </div>
        </fieldset>

        <div class="aksi" style="margin-top: 16px;">
            <button class="btn" type="submit">Simpan Website</button>
            <a class="btn" style="background: #6b7280;" href="{{ route('sites.index') }}">Batal</a>
        </div>
    </form>
</div>
@endsection
