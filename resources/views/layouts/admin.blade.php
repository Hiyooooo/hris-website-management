<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Panel') | HRIS Admin</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: #f3f4f6; color: #1f2937; line-height: 1.5; }
        a { color: inherit; text-decoration: none; }
        img[hidden] { display: none; }
        .top { background: #111827; color: #fff; padding: 14px 24px; font-weight: 700; }
        .wrap { max-width: 1100px; margin: 0 auto; padding: 28px 20px 60px; }
        h1 { font-size: 26px; margin: 8px 0 4px; }
        .sub, .muted { color: #6b7280; font-size: 14px; }
        .back { font-size: 14px; color: #2563eb; }
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 18px; font-size: 14px; }
        .alert.ok { background: #dcfce7; color: #166534; }
        .alert.err { background: #fee2e2; color: #991b1b; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px; margin-top: 22px; }
        .kartu { background: #fff; border-radius: 12px; padding: 20px; display: flex; flex-direction: column; gap: 6px; }
        .kepala { display: flex; justify-content: space-between; align-items: center; }
        .badge { font-size: 12px; padding: 2px 10px; border-radius: 99px; }
        .badge.ok { background: #dcfce7; color: #166534; }
        .badge.off { background: #e5e7eb; color: #4b5563; }
        .swatch { width: 28px; height: 28px; border-radius: 6px; border: 1px solid #d1d5db; }
        .btn { display: inline-block; margin-top: 8px; background: #2563eb; color: #fff; padding: 9px 16px; border: 0; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; text-align: center; }
        .layout { display: grid; grid-template-columns: 1.4fr 1fr; gap: 24px; margin-top: 20px; align-items: start; }
        .form fieldset { background: #fff; border: 0; border-radius: 12px; padding: 20px; margin-bottom: 16px; }
        .form legend { font-weight: 700; padding: 0; margin-bottom: 12px; float: left; width: 100%; }
        .form legend + * { clear: both; }
        label { display: block; font-size: 14px; font-weight: 600; margin: 12px 0 4px; }
        input[type=text], input[type=email], input[type=url], textarea, input[type=file] { width: 100%; padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 8px; font: inherit; background: #fff; }
        textarea { min-height: 76px; resize: vertical; }
        .warna { display: flex; gap: 10px; align-items: center; }
        .warna input[type=color] { width: 48px; height: 40px; padding: 2px; border: 1px solid #d1d5db; border-radius: 8px; background: #fff; cursor: pointer; }
        .warna input[type=text] { max-width: 150px; font-family: monospace; }
        .cek { display: flex; gap: 8px; align-items: center; font-weight: 400; margin-top: 8px; }
        .hint { font-size: 12px; color: #6b7280; margin-top: 4px; display: block; }
        .err { color: #b91c1c; font-size: 13px; margin-top: 4px; display: block; }
        .dua { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .aksi { display: flex; gap: 12px; align-items: center; }
        .aksi .btn { padding: 12px 28px; font-size: 15px; margin: 0; }
        .preview { position: sticky; top: 20px; background: #fff; border-radius: 12px; padding: 20px; --warna: #2563eb; }
        .preview h3 { font-size: 13px; color: #6b7280; margin-bottom: 14px; }
        .pv-nav { display: flex; align-items: center; gap: 10px; font-weight: 700; padding-bottom: 14px; border-bottom: 1px solid #e5e7eb; margin-bottom: 18px; color: var(--warna); }
        .pv-nav img { max-height: 32px; max-width: 120px; }
        .pv-judul { font-size: 22px; line-height: 1.2; margin-bottom: 8px; }
        .pv-desk { color: #6b7280; font-size: 14px; margin-bottom: 16px; }
        .pv-cta { display: inline-block; background: var(--warna); color: #fff; padding: 9px 18px; border-radius: 8px; font-size: 14px; font-weight: 600; }
        @media (max-width: 860px) { .layout { grid-template-columns: 1fr; } .preview { position: static; } .dua { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="top"><a href="{{ route('sites.index') }}">HRIS Admin</a></div>

    <main class="wrap">
        @if (session('success'))
            <div class="alert ok">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert err">Ada isian yang perlu diperbaiki. Cek pesan merah di bawah kolomnya.</div>
        @endif

        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>