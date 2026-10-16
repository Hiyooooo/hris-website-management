@extends('layouts.admin')

@section('title', 'Kunci Akses API ' . $site->name)

@section('content')
<a class="back" href="{{ route('sites.index') }}">Kembali ke Daftar Website</a>
<h1>Kunci Akses API: {{ $site->name }}</h1>
<p class="sub">{{ $site->domain }} · ID {{ $site->site_id }}</p>

<nav class="tabs">
    <a class="tab" href="{{ route('sites.content.edit', $site) }}">Konten & Tampilan</a>
    <a class="tab" href="{{ route('sites.seo.edit', $site) }}">SEO & OpenGraph</a>
    <a class="tab active" href="{{ route('sites.keys.show', $site) }}">Kunci Akses</a>
</nav>

{{-- Status Koneksi --}}
@if ($site->last_used_at)
    <div class="alert ok" style="display: flex; align-items: center; justify-content: space-between;">
        <div>
            <strong>Status: Front-end Terhubung.</strong> API terakhir diakses oleh Next.js <strong>{{ $site->last_used_at->diffForHumans() }}</strong> ({{ $site->last_used_at->format('d M Y H:i:s') }}).
        </div>
        <span class="badge ok">Aktif</span>
    </div>
@else
    <div class="alert" style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a;">
        <strong>Peringatan: Kunci belum pernah dipakai.</strong> Front-end Next.js belum pernah terhubung menggunakan kunci ini. Silakan pasang konfigurasi di bawah ke file <code>.env</code> website Anda.
    </div>
@endif

<div class="layout" style="grid-template-columns: 1.2fr 1fr;">
    <div>
        {{-- Kredensial Tunggal --}}
        <div style="background: #fff; border-radius: 12px; padding: 22px; margin-bottom: 20px;">
            <h2 style="font-size: 18px; margin-bottom: 16px;">Kredensial API Header</h2>

            <label for="f_site_id">X-Site-Id</label>
            <div style="display: flex; gap: 8px;">
                <input id="f_site_id" type="text" readonly value="{{ $site->site_id }}" style="background: #f9fafb; font-family: monospace;">
                <button class="btn" type="button" onclick="copyText('{{ $site->site_id }}', this)" style="margin: 0; white-space: nowrap;">Salin</button>
            </div>

            <label for="f_secret" style="margin-top: 14px;">X-Site-Secret</label>
            <div style="display: flex; gap: 8px;">
                <input id="f_secret" type="password" readonly value="{{ $plainSecret }}" style="background: #f9fafb; font-family: monospace;">
                <button class="btn" type="button" onclick="toggleSecret()" style="background: #4b5563; margin: 0; white-space: nowrap;" id="btn_toggle">Lihat</button>
                <button class="btn" type="button" onclick="copyText('{{ $plainSecret }}', this)" style="margin: 0; white-space: nowrap;">Salin</button>
            </div>
            <span class="hint">Secret disimpan terenkripsi di database dan hanya digunakan di server Next.js.</span>

            <label for="f_api_url" style="margin-top: 14px;">API Base URL</label>
            <div style="display: flex; gap: 8px;">
                <input id="f_api_url" type="text" readonly value="{{ url('/') }}" style="background: #f9fafb; font-family: monospace;">
                <button class="btn" type="button" onclick="copyText('{{ url('/') }}', this)" style="margin: 0; white-space: nowrap;">Salin</button>
            </div>
        </div>

        {{-- Snippet .env Siap Pakai --}}
        <div style="background: #fff; border-radius: 12px; padding: 22px; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h2 style="font-size: 18px;">Konfigurasi .env (Next.js)</h2>
                <button class="btn" type="button" onclick="copyEnv(this)" style="margin: 0; padding: 6px 14px; font-size: 13px;">Salin Semua .env</button>
            </div>
            <p class="sub" style="margin-bottom: 12px;">Salin baris berikut ke file <code>.env.local</code> pada project Next.js website ini:</p>
            <pre id="env_code" style="background: #111827; color: #10b981; padding: 14px; border-radius: 8px; font-size: 13px; line-height: 1.6; overflow-x: auto; font-family: monospace;">API_URL={{ url('/') }}
SITE_ID={{ $site->site_id }}
SITE_SECRET={{ $plainSecret }}</pre>
            <p class="hint" style="color: #b91c1c; margin-top: 8px;">
                <strong>Perhatian (Aturan Keamanan GEMINI.md):</strong> Dilarang menggunakan prefix <code>NEXT_PUBLIC_</code>. Kunci ini hanya boleh dibaca di Server Component (bukan browser).
            </p>
        </div>

        {{-- Regenerate Key --}}
        <div style="background: #fff; border-radius: 12px; padding: 22px; border-left: 4px solid #ef4444;">
            <h2 style="font-size: 18px; color: #991b1b; margin-bottom: 6px;">Pencabutan & Buat Ulang Kunci (Regenerate Key)</h2>
            <p class="sub" style="margin-bottom: 14px; color: #4b5563;">
                Gunakan fitur ini jika kunci dicurigai bocor ke publik atau sebagai rotasi keamanan berkala. 
                <strong>Kunci lama akan langsung mati seketika (0 detik).</strong>
            </p>
            <form method="POST" action="{{ route('sites.keys.regenerate', $site) }}" onsubmit="return confirm('PERINGATAN!\n\nKunci lama akan langsung dicabut seketika. Website Next.js akan mengalami 401 sampai Anda memperbarui .env.\n\nApakah Anda yakin ingin membuat kunci baru sekarang?');">
                @csrf
                <button class="btn" type="submit" style="background: #dc2626;">
                    Buat Ulang Kunci (Regenerate)
                </button>
            </form>
        </div>
    </div>

    {{-- Panduan Integrasi Front-end --}}
    <aside style="background: #fff; border-radius: 12px; padding: 22px; position: sticky; top: 20px;">
        <h3 style="font-size: 16px; margin-bottom: 12px; color: #111827;">Cara Integrasi di Next.js (App Router)</h3>
        
        <p class="sub" style="margin-bottom: 10px;">
            Buat file <code>lib/api.ts</code> di project Next.js Anda:
        </p>

        <pre style="background: #1f2937; color: #f3f4f6; padding: 12px; border-radius: 8px; font-size: 12px; line-height: 1.5; overflow-x: auto; font-family: monospace;">import 'server-only';

export async function getConfig() {
  const res = await fetch(`${process.env.API_URL}/api/v1/site/config`, {
    headers: {
      'X-Site-Id': process.env.SITE_ID!,
      'X-Site-Secret': process.env.SITE_SECRET!,
    },
    next: { revalidate: 120 } // ISR 2 menit
  });

  if (!res.ok) {
    throw new Error(`Gagal fetch config: ${res.status}`);
  }

  return res.json();
}</pre>

        <div style="margin-top: 16px; font-size: 13px; color: #4b5563; line-height: 1.5;">
            <p><strong>Ketahanan Front-end:</strong></p>
            <p style="margin-top: 4px;">
                Jika backend mati atau kunci belum diganti, Next.js otomatis memakai cache ISR lama atau nilai fallback default.
            </p>
        </div>
    </aside>
</div>
@endsection

@push('scripts')
<script>
    function copyText(text, btn) {
        navigator.clipboard.writeText(text).then(() => {
            const original = btn.textContent;
            btn.textContent = 'Tersalin!';
            btn.style.background = '#16a34a';
            setTimeout(() => {
                btn.textContent = original;
                btn.style.background = '';
            }, 2000);
        });
    }

    function toggleSecret() {
        const input = document.getElementById('f_secret');
        const btn = document.getElementById('btn_toggle');
        if (input.type === 'password') {
            input.type = 'text';
            btn.textContent = 'Sembunyikan';
        } else {
            input.type = 'password';
            btn.textContent = 'Lihat';
        }
    }

    function copyEnv(btn) {
        const text = document.getElementById('env_code').innerText;
        copyText(text, btn);
    }
</script>
@endpush
