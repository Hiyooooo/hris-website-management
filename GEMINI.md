# GEMINI.md: Proyek HRIS Landing Pages + Headless CMS

Dokumen ini adalah sumber kebenaran untuk AI agent yang mengerjakan proyek ini. Baca seluruhnya sebelum menulis kode. Jika ada konflik antara dokumen ini dan asumsi Anda, ikuti dokumen ini. Jika ada hal yang tidak tercakup, lihat bagian "Keputusan Terbuka" dan tanyakan, jangan menebak.

---

## 1. Ringkasan Proyek

Dua komponen:

1. **Admin Panel (Laravel 11 + SQLite)**: Headless CMS terpusat yang mengatur 10 website sekaligus.
2. **10 Landing Page (Next.js 14/15 + Tailwind CSS)**: Website promosi produk HRIS. Setiap situs punya desain, persona, dan branding berbeda (mis. Bento Grid, Fintech Trust, Mobile-First, Corporate Compliance). Next.js **hanya front-end**.

Tujuan bisnis: **konversi**. Pengunjung membaca landing page dan artikel, lalu klik CTA yang membuka WhatsApp ke tim sales.

Bukan dashboard internal karyawan. Bukan aplikasi HRIS yang berfungsi.

### 5 Modul HRIS (wajib tampil di setiap landing page)

Hanya **konten pemasaran**. Tidak ada fitur yang dibangun.

1. **Absensi**: Clock-in GPS, geofencing, shift kerja.
2. **Gaji (Payroll)**: PPh 21, BPJS, kalkulasi otomatis, slip digital.
3. **Exam**: Assessment online, evaluasi kinerja, proctoring.
4. **Library**: SOP kantor, arsip dokumen perusahaan, e-learning.
5. **Perizinan**: Approval cuti, izin sakit, lembur.

---

## 2. Keputusan yang Sudah Final (jangan diubah tanpa persetujuan)

| #   | Keputusan                                                                                                                        |
| --- | -------------------------------------------------------------------------------------------------------------------------------- |
| 1   | Next.js **tanpa API route apa pun**. Tidak ada `app/api/*`, tidak ada webhook, tidak ada `revalidateTag` on-demand.              |
| 2   | Pembaruan data murni lewat **ISR berbasis waktu**: `next: { revalidate: 120 }` pada setiap `fetch` (boleh 60-120).               |
| 3   | `X-Site-Secret` **hanya di server**. Tidak boleh berprefix `NEXT_PUBLIC_`, tidak boleh dipanggil dari Client Component.          |
| 4   | Status **Draft hanya untuk artikel**. Konfigurasi situs (warna, hero, SEO) langsung berlaku saat disimpan; cukup validasi ketat. |
| 5   | **Tidak ada auto-publish** artikel. Semua artikel melewati Draft, review manusia, lalu Publish.                                  |
| 6   | Satu artikel dimiliki **satu situs** (hindari duplicate content).                                                                |
| 7   | Konten 5 modul HRIS **hardcoded** di tiap landing page, bukan dari admin.                                                        |
| 8   | Warna tema disuntikkan lewat **CSS variable** dari data admin.                                                                   |
| 9   | CTA membuka WhatsApp (`wa.me`) dengan pesan terformat.                                                                           |

Server Component yang `fetch` ke Laravel **bukan** API route; itu diperbolehkan. Begitu juga `sitemap.ts`, `robots.ts`, dan `generateMetadata`.

---

## 3. Arsitektur

```
Pengunjung -> Next.js (Server Components, ISR cache) -> [fetch tiap >=60-120 dtk] -> Laravel API -> SQLite
Admin      -> Laravel Admin Panel -> SQLite
Admin      -> Laravel -> Gemini API (via queue job) -> artikel Draft
```

- Pengunjung tidak pernah menyentuh Laravel secara langsung.
- Beban ke Laravel kecil: maksimal sekitar 1 request per endpoint per menit per situs, karena revalidasi hanya terjadi bila ada pengunjung setelah cache kedaluwarsa.
- Jika Laravel mati, Next.js tetap menyajikan data cache lama. Jangan melempar error ke pengunjung dari bagian yang bisa diberi fallback.

---

## 4. Backend: Laravel 11 + SQLite

### 4.1 Admin Panel: Alur UX

1. **Dashboard (Site List Hub)**: daftar semua website sebagai kartu/tabel + tombol `+ Tambah Website`. Kartu menampilkan: nama, status, terakhir diakses API (`last_used_at`), jumlah artikel published/draft, terakhir diubah dan oleh siapa, badge peringatan (mis. "key belum pernah dipakai", "SEO belum lengkap").
2. **Workspace per situs** (klik satu situs), dengan tab:
    - 🎨 **Konten & Tampilan**: logo, favicon, warna primer (hex), headline hero, sub-headline, teks CTA, nomor WhatsApp + template pesan, footer, kontak & link sosial media, ID Google Analytics, ID Meta Pixel, domain situs.
    - 🔍 **SEO & OpenGraph**: meta title, meta description, keywords, URL banner OG, dengan live preview kartu media sosial.
    - ✍️ **Artikel (Gemini)**: generate, Draft, review/edit, Publish. Hanya untuk situs ini.
    - 🔑 **Kunci Akses**: tampilkan `X-Site-Id`, salin Secret Key, Regenerate Key.
3. **Tambahan di dashboard**: tombol "Clone dari situs lain" (salin konfigurasi + SEO), soft delete situs.
4. Tampilkan catatan di admin: _"Perubahan tampil di situs dalam ±2 menit."_

> Rekomendasi: gunakan **Filament** untuk form, color picker, upload, dan tabel. (Belum dikonfirmasi; lihat Keputusan Terbuka.)

### 4.2 Skema Database (SQLite)

Aktifkan **WAL mode**. Gunakan foreign key. Semua tabel punya `created_at`, `updated_at`.

**sites**

- `id`, `site_id` (unik, mis. `01-talentacloud`), `name`, `domain`, `is_active` (bool)
- `secret` (terenkripsi, cast `encrypted`), `previous_secret` (terenkripsi, nullable), `previous_secret_expires_at` (nullable)
- `last_used_at` (nullable), `deleted_at` (soft delete)

**site_contents** (1:1 dengan sites)

- `site_id` (FK), `logo_url`, `favicon_url`, `primary_color` (`#RRGGBB`), `hero_headline`, `hero_subheadline`
- `cta_text`, `whatsapp_number`, `whatsapp_message_template`, `footer_text`
- `contact_email`, `contact_phone`, `social_links` (JSON), `ga_id`, `meta_pixel_id`

**seo_settings** (1:1 dengan sites)

- `site_id` (FK), `meta_title`, `meta_description`, `keywords`, `og_image_url`

**articles**

- `id`, `site_id` (FK), `title`, `slug` (unik per situs), `excerpt`, `content`, `cover_image_url`
- `topic`, `status` (`draft` | `published` | `archived`), `published_at`, `created_by`, `updated_by`
- Metadata Gemini: `ai_model`, `tokens_used`

**activity_logs**: siapa mengubah apa dan kapan (warna, hero, key, publish artikel). Boleh pakai `spatie/laravel-activitylog`.

**users**: login admin, dengan role `admin` dan `editor`. Editor **tidak boleh** melihat atau me-regenerate key.

**settings** (global): API key Gemini, prompt dasar, default nomor WhatsApp.

> Catatan relasi: karena satu artikel dimiliki satu situs, relasi cukup `articles.site_id`. Tabel `article_distributions` hanya perlu bila nanti satu artikel ingin tayang di banyak situs. Saat ini tidak dipakai.

### 4.3 Keamanan Secret Key

- Header wajib: `X-Site-Id`, `X-Site-Secret`.
- Secret disimpan **terenkripsi** (bukan hash), karena admin bisa menyalinnya kembali. Bandingkan dengan `hash_equals`.
- Format contoh: `sec_<slug>_<random panjang>` dari `Str::random(48)` atau lebih.
- **Rotasi tanpa downtime**: saat Regenerate, simpan key lama di `previous_secret` dengan `previous_secret_expires_at = now() + 24 jam`. Middleware menerima key baru **atau** key lama yang belum kedaluwarsa.
- `last_used_at` diperbarui **maksimal sekali per 5 menit** per situs (hindari tulis SQLite di tiap request).

### 4.4 Middleware `VerifySiteSecret`

1. Cek header `X-Site-Id` dan `X-Site-Secret` ada. Jika tidak, **401**.
2. Cari situs berdasarkan `site_id`, aktif, dan tidak soft-deleted. Jika tidak ketemu, **401** (pesan sama, jangan bocorkan mana yang salah).
3. Bandingkan secret dengan `hash_equals` (key saat ini, lalu `previous_secret` bila belum kedaluwarsa). Jika tidak cocok, **401**.
4. Simpan objek situs: `$request->attributes->set('current_site', $site)`. Controller mengambilnya dari sini **tanpa query ulang**.
5. Terapkan rate limit **120 request/menit per `X-Site-Id`**.

### 4.5 Kontrak REST API

Semua di bawah middleware di atas. Prefix `/api/v1`. Respons JSON. Bungkus data seperlunya, jangan bocorkan field internal (`secret`, `created_by`, dll.).

**`GET /api/v1/site/config`**

```json
{
    "site": {
        "id": "01-talentacloud",
        "name": "TalentaCloud",
        "domain": "talentacloud.id"
    },
    "branding": {
        "logo_url": "",
        "favicon_url": "",
        "primary_color": "#0284c7"
    },
    "hero": { "headline": "", "subheadline": "", "cta_text": "" },
    "cta": {
        "whatsapp_number": "",
        "whatsapp_message_template": "",
        "whatsapp_url": ""
    },
    "contact": { "email": "", "phone": "", "social_links": {} },
    "footer_text": "",
    "tracking": { "ga_id": null, "meta_pixel_id": null },
    "seo": {
        "meta_title": "",
        "meta_description": "",
        "keywords": [],
        "og_image_url": ""
    }
}
```

**`GET /api/v1/site/articles`**: daftar artikel `status = published` milik situs pemanggil, terbaru dulu. Field: `slug`, `title`, `excerpt`, `cover_image_url`, `published_at`. Sertakan paginasi sederhana.

**`GET /api/v1/site/articles/{slug}`**: konten lengkap satu artikel `published` milik situs pemanggil. Jika bukan milik situs itu atau belum published, **404**.

Validasi input admin:

- `primary_color`: regex `^#[0-9A-Fa-f]{6}$`.
- URL: validasi format dan skema `https`.
- Batasi panjang teks (headline, meta description, dll.).

### 4.6 Cache Laravel

- Bungkus respons config dengan `Cache::remember("site:{$id}:config", ...)`.
- `Cache::forget` saat admin menyimpan konten/SEO atau mem-publish/unpublish artikel.
- Driver `file`/`database` tidak mendukung tags; gunakan key per situs dan `forget` eksplisit.
- Opsional (prioritas rendah): `ETag` + respons `304`.

### 4.7 Engine Artikel Gemini

- Panggil Gemini lewat **queue job** (jangan sinkron di request; bisa 10-30 detik). Sediakan retry, penanganan rate limit, dan error yang tampil jelas di UI.
- API key Gemini dari `settings`/`.env`, jangan hardcoded dan jangan ke repo.
- **10 topik HRIS** (absensi & geofencing, payroll, PPh 21, BPJS, exam & assessment, library & SOP, perizinan/cuti/lembur, kepatuhan ketenagakerjaan, produktivitas HR, transformasi digital HR). Prompt dibuat dinamis dengan variasi acak (sudut pandang, struktur, gaya) agar tidak repetitif.
- Kontrol di UI: topik, bahasa, nada, kata kunci target, panjang artikel.
- Cek kemiripan dengan artikel yang sudah ada di situs yang sama.
- Alur: `generate -> draft -> admin review/edit -> publish`. Slug bisa diedit. Catat `tokens_used`.
- Artikel AI wajib direview manusia terutama untuk angka dan regulasi (tarif PPh 21, aturan BPJS). Tampilkan pengingat ini di UI editor.

---

## 5. Frontend: Next.js 14/15 + Tailwind

### 5.1 Aturan keras

- **Dilarang** membuat `app/api/*`, route handler, atau server action yang menjadi endpoint publik.
- **Dilarang** memanggil Laravel dari Client Component atau browser.
- Variabel env: `API_URL`, `SITE_ID`, `SITE_SECRET` (**tanpa** `NEXT_PUBLIC_`).
- Tandai modul fetch dengan `import 'server-only'`.
- Next.js 15 tidak men-cache `fetch` secara default, jadi **selalu tulis eksplisit** `next: { revalidate: 120 }`.

```ts
// lib/api.ts
import "server-only";

export async function getConfig() {
    const res = await fetch(`${process.env.API_URL}/api/v1/site/config`, {
        headers: {
            "X-Site-Id": process.env.SITE_ID!,
            "X-Site-Secret": process.env.SITE_SECRET!,
        },
        next: { revalidate: 120 },
    });
    if (!res.ok) throw new Error(`config ${res.status}`);
    return res.json();
}
```

### 5.2 Tema dinamis

Suntikkan warna di root layout:

```tsx
<html style={{ '--primary': config.branding.primary_color } as React.CSSProperties}>
```

Semua tombol, border, badge memakai variabel itu (`bg-[var(--primary)]`). Jangan hardcode warna merek. Jika butuh opacity Tailwind (`bg-primary/10`), konversi hex ke channel RGB di server.

### 5.3 Halaman

- `/`: landing page: hero, 5 modul HRIS, CTA, footer.
- `/blog`: daftar artikel published.
- `/blog/[slug]`: artikel. Gunakan `generateStaticParams` bila praktis; slug baru tetap harus bisa dilayani saat runtime (ISR).
- `sitemap.ts`, `robots.ts`, tag `canonical` untuk artikel, `generateMetadata` dari data `seo`.
- Pasang GA dan Meta Pixel hanya jika ID tersedia.

### 5.4 CTA WhatsApp

`https://wa.me/<nomor>?text=<pesan ter-encode>`. Bentuk URL boleh dihitung di server dan dikirim ke komponen. Pesan template boleh memuat nama situs.

### 5.5 Ketahanan

- Jika revalidasi gagal, tetap sajikan data lama.
- `next build` tidak boleh gagal total karena API sementara tak terjangkau: beri retry dan fallback.
- Setiap situs punya desain unik, tetapi **kontrak data dan struktur fetch sama**. Disarankan satu basis kode/template bersama yang di-skin.

### 5.6 Konten 5 modul

Hardcoded di tiap situs, dengan gaya penyampaian sesuai persona situs. Tetap wajib memuat kelima modul dan poin fitur di Bagian 1.

---

## 6. Standar Kerja untuk Agent

- Kerjakan per tahap sesuai urutan di Bagian 7. Jangan melompat ke fitur fase berikutnya.
- Jangan menambah fitur di luar dokumen ini ("Di Luar Lingkup" di Bagian 8).
- Setiap endpoint dan middleware punya test (Pest/PHPUnit): 401 tanpa header, 401 key salah, 401 situs nonaktif, sukses dengan key lama dalam masa rotasi, 404 artikel situs lain, validasi hex.
- Jangan commit `.env`, secret, atau API key. Sediakan `.env.example`.
- Gunakan Form Request untuk validasi, API Resource untuk bentuk respons.
- Perubahan skema lewat migration. Jangan edit file SQLite langsung.
- Backup rutin file `.sqlite`.
- Commit kecil dengan pesan yang jelas. Tulis ringkasan apa yang diubah dan mengapa.
- Jika spesifikasi ambigu, berhenti dan ajukan pertanyaan di akhir pekerjaan, jangan berasumsi diam-diam.

### Definition of Done (per fitur)

1. Sesuai kontrak di dokumen ini.
2. Test lulus.
3. Tidak ada secret bocor ke browser atau respons API.
4. Validasi input ada.
5. Perubahan tercatat di activity log (bila menyangkut data admin).

---

## 7. Timeline (2 orang: Arya & Adika)

| Tanggal   | Tahap              | Arya                                                                       | Adika                                                                        |
| --------- | ------------------ | -------------------------------------------------------------------------- | ---------------------------------------------------------------------------- |
| 07 Okt    | Perancangan        | ERD SQLite                                                                 | Flowchart sistem                                                             |
| 09-10 Okt | Manajemen konten   | UI form konten (logo, color picker, hero/CTA, field tambahan) + controller | Generator Secret Key, middleware `VerifySiteSecret`, endpoint `/site/config` |
| 12 Okt    | SEO & OpenGraph    | UI form SEO + live preview kartu sosial                                    | Payload SEO di API + validasi                                                |
| 14-17 Okt | Gemini & artikel   | Endpoint `/site/articles`, UI editor draft -> publish                      | Integrasi Gemini, prompt 10 topik, queue job                                 |
| 18-20 Okt | Testing & keamanan | Functional test dashboard, UX, validasi regenerasi key                     | Audit keamanan API (uji bypass), rate limit, optimasi SQLite                 |
| 21 Okt    | Deployment         | UAT akhir, dokumentasi integrasi front-end                                 | Server production, SQLite live, build deployment                             |

Tambahan yang perlu dijadwalkan: rotasi key, activity log, cache Laravel (Adika); hub dashboard dan field konten tambahan (Arya). Jika waktu mepet, korbankan lebih dulu: live preview OG, clone situs, tracking.

---

## 8. Di Luar Lingkup (jangan dikerjakan)

- Auto-publish atau penjadwalan artikel otomatis (n8n atau scheduler).
- API route atau webhook revalidate di Next.js.
- Draft/preview untuk konfigurasi situs.
- Fitur HRIS yang berfungsi (absensi nyata, payroll nyata, dsb.).
- Satu artikel tayang di banyak situs.
- Sistem lead/CRM penuh.

Opsional bila waktu tersisa dan **hanya atas persetujuan**: tracking klik CTA, media library dengan konversi WebP, export/import konfigurasi JSON.

---

## 9. Keputusan Terbuka (tanyakan ke manusia, jangan asumsikan)

1. Admin panel memakai Filament atau Blade/Livewire buatan sendiri?
2. Hosting Next.js: proses sendiri per situs (VPS/Docker) atau Vercel? Memengaruhi penanganan ISR dan build.
3. Berapa pengguna admin, dan apakah role `admin`/`editor` cukup?
4. Apakah 10 situs berasal dari satu basis kode bersama yang di-skin, atau 10 repositori terpisah? Dan siapa yang membangun front-end?
5. Apakah perlu tracking klik CTA sejak awal (bila ya, tabel masuk ke ERD sekarang)?
6. Bahasa konten situs dan artikel: hanya Indonesia atau tambah bahasa lain?
