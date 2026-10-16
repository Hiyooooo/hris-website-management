@extends('layouts.admin')

@section('title', 'Daftar Website')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
    <div>
        <h1>Daftar Website</h1>
        <p class="sub">Pilih website untuk mengatur tampilan, SEO, dan kredensial aksesnya.</p>
    </div>
    <a class="btn" href="{{ route('sites.create') }}">+ Tambah Website</a>
</div>

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
            Konten: {{ $site->content ? 'Sudah diisi' : 'Belum diisi' }} · 
            SEO: {{ $site->seo ? 'Sudah diisi' : 'Belum diisi' }}
        </p>

        @if ($site->content)
        <span
            class="swatch"
            data-color="{{ $site->content->primary_color ?? '#2563eb' }}"></span>
        @endif

        <p class="muted" style="font-size: 12px; margin-top: 4px;">
            @if ($site->last_used_at)
                API aktif: {{ $site->last_used_at->diffForHumans() }}
            @else
                <span style="color: #b45309;">Key belum pernah dipakai</span>
            @endif
        </p>

        <div style="display: flex; gap: 8px; margin-top: 8px;">
            <a class="btn" style="flex: 1;" href="{{ route('sites.content.edit', $site) }}">
                {{ $site->content ? 'Edit konten' : 'Tambah konten' }}
            </a>
            <a class="btn" style="background: #4b5563;" href="{{ route('sites.seo.edit', $site) }}">
                SEO
            </a>
            <a class="btn" style="background: #0f766e;" href="{{ route('sites.keys.show', $site) }}">
                Kunci
            </a>
        </div>
    </article>
    @empty
    <div style="grid-column: 1 / -1; background: #fff; border-radius: 12px; padding: 32px; text-align: center;">
        <p class="sub" style="font-size: 15px; margin-bottom: 16px;">Belum ada website yang terdaftar di sistem.</p>
        <a class="btn" href="{{ route('sites.create') }}">+ Daftarkan Website Pertama</a>
    </div>
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