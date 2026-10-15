@extends('layouts.admin')

@section('title', 'Daftar Website')

@section('content')
<h1>Daftar Website</h1>
<p class="sub">Pilih website untuk mengatur tampilan dan kontennya.</p>

<div class="grid">
    @forelse ($sites as $site)
    <article class="kartu">
        <div class="kepala">
            <h2>{{ $site->name }}</h2>

            <span class="badge {{ $site->is_active ? 'ok' : 'off' }}">
                {{ $site->is_active ? 'Aktif' : 'Nonaktif' }}
            </span>
        </div>

        <p class="muted">{{ $site->domain }}</p>

        <p class="muted">
            Konten: {{ $site->content ? 'sudah diisi' : 'belum diisi' }} · 
            SEO: {{ $site->seo ? 'sudah diisi' : 'belum diisi' }}
        </p>

        @if ($site->content)
        <span
            class="swatch"
            data-color="{{ $site->content->primary_color ?? '#2563eb' }}"></span>
        @endif

        <div style="display: flex; gap: 8px; margin-top: 8px;">
            <a class="btn" style="flex: 1;" href="{{ route('sites.content.edit', $site) }}">
                {{ $site->content ? 'Edit konten' : 'Tambah konten' }}
            </a>
            <a class="btn" style="background: #4b5563;" href="{{ route('sites.seo.edit', $site) }}">
                SEO
            </a>
        </div>
    </article>
    @empty
    <p>Belum ada website. Jalankan seeder atau tambahkan website terlebih dahulu.</p>
    @endforelse
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.swatch[data-color]').forEach((el) => {
        el.style.backgroundColor = el.dataset.color;
    });
</script>
@endpush