<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', $title ?? config('app.name', 'Pegasus Academy'))</title>
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
            
            /* Header icon badges positioning - higher specificity */
            header .absolute.-top-1.-right-1,
            header .absolute.-top-1.-right-1.min-w-\[18px\].h-\[18px\] {
                top: 10px !important;
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

    @include('partials.disable-context-menu')

    {{--
        اختصارات أدوات المطوّر وسحب الصور: معطّلة خارج بيئة local حتى لا يعيق التطوير.
        لا يمكن منع Inspect فعلياً من المتصفح.
    --}}
    @unless(app()->environment('local'))
    <script>
        (function () {
            'use strict';
            document.addEventListener('dragstart', function (e) {
                if (e.target instanceof HTMLImageElement) {
                    e.preventDefault();
                }
            }, { capture: true });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'F12') {
                    e.preventDefault();
                    return false;
                }
                if ((e.ctrlKey || e.metaKey) && e.shiftKey) {
                    var k = e.key;
                    if (k === 'I' || k === 'J' || k === 'C' || k === 'K') {
                        e.preventDefault();
                    }
                }
                if ((e.ctrlKey || e.metaKey) && (e.key === 'u' || e.key === 'U') && !e.shiftKey) {
                    e.preventDefault();
                }
            }, { capture: true });
        })();
    </script>
    @endunless

    {{--
        تقليل احتمال نسخ لقطة الشاشة إلى الحافظة + تنبيه عند مفتاح Print Screen (لا يعمل على كل الأنظمة/المتصفحات).
        visibilitychange: يخفي وضوح الصفحة عند إخفاء التاب أو تصغير النافذة — ليس مرتبطاً حصرياً بلقطات الشاشة.
    --}}
    @unless(app()->environment('local'))
    <script>
        (function () {
            'use strict';
            function isPrintScreenKey(e) {
                return e.key === 'PrintScreen' || e.code === 'PrintScreen';
            }
            function warnPrintScreen() {
                try {
                    if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
                        navigator.clipboard.writeText('').catch(function () {});
                    }
                } catch (err) {}
                alert('ممنوع تصوير الشاشة');
            }
            document.addEventListener('keyup', function (e) {
                if (isPrintScreenKey(e)) {
                    warnPrintScreen();
                }
            }, { capture: true });
            document.addEventListener('keydown', function (e) {
                if (isPrintScreenKey(e)) {
                    e.preventDefault();
                }
            }, { capture: true });
            document.addEventListener('visibilitychange', function () {
                if (document.hidden) {
                    document.body.style.filter = 'blur(20px)';
                } else {
                    document.body.style.filter = 'none';
                }
            });
        })();
    </script>
    @endunless
</body>
</html>

