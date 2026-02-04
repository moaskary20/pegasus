<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Pegasus Academy'))</title>
    @stack('head')

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=tajawal:300,400,500,700,800&display=swap" rel="stylesheet" />

    {{-- Styles --}}
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            :root { --brand: #2c004d; }
            body { font-family: 'Tajawal', system-ui, -apple-system, Segoe UI, Roboto, sans-serif; }
            
            /* Header icon badges positioning - overrides */
            .absolute.-top-1.-right-1 {
                top: 10px !important;
            }
            .absolute.-top-1.-right-1.min-w-\[18px\].h-\[18px\] {
                margin-top: 17px;
            }
        </style>
    @endif

    @stack('head_scripts')
    {{-- Alpine (public site) --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-white text-slate-900">
    <x-site.header />

    <main class="min-h-[60vh]">
        @hasSection('content')
            @yield('content')
        @else
            {{ $slot ?? '' }}
        @endif
    </main>

    <x-site.footer />

    @if(app()->environment('local'))
        <div class="fixed bottom-2 left-2 px-2 py-1 rounded-lg border bg-white text-[10px] text-slate-500 shadow-sm opacity-80">
            build: {{ $__build_id ?? 'n/a' }}
        </div>
    @endif
</body>
</html>

