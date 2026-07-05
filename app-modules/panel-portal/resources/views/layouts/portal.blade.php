<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Brainiac' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400..700;1,400..600&family=Source+Serif+4:ital,opsz,wght@0,8..60,300..700;1,8..60,300..500&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite('app-modules/panel-portal/resources/css/portal.css')
</head>
<body class="min-h-screen bg-night text-ink font-sans text-[14px] leading-[1.5] antialiased">
    <x-panel-portal::topbar />

    {{ $slot }}
</body>
</html>
