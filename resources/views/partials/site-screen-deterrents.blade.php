{{--
    حزمة تثبيطية فقط: لا تمنع تصوير الشاشة أو أدوات النظام.
    تُحمَّل خارج بيئة local (انظر layouts.site).
--}}
<style>
    html.site-content-protect body {
        -webkit-user-select: none;
        user-select: none;
        -webkit-touch-callout: none;
    }
    html.site-content-protect input:not([type="checkbox"]):not([type="radio"]):not([type="range"]),
    html.site-content-protect textarea,
    html.site-content-protect select,
    html.site-content-protect [contenteditable="true"] {
        -webkit-user-select: text;
        user-select: text;
    }
    html.site-content-protect img {
        -webkit-user-drag: none;
    }
    html.site-content-protect body.site-privacy-blur,
    html.site-content-protect body.site-window-blur {
        filter: blur(22px);
    }
    @media print {
        html.site-content-protect body * {
            visibility: hidden !important;
        }
        html.site-content-protect body::before {
            content: 'غير مسموح بطباعة أو حفظ هذه الصفحة.';
            visibility: visible !important;
            position: fixed;
            inset: 0;
            z-index: 2147483647;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem;
            font-size: 1.125rem;
            font-weight: 700;
            color: #1e293b;
            background: #fff;
        }
    }
</style>
<script>
    (function () {
        'use strict';

        function allowClipboardFromField(target) {
            if (!target || typeof target.closest !== 'function') {
                return false;
            }
            return !!target.closest('input, textarea, select, [contenteditable="true"]');
        }

        document.addEventListener('contextmenu', function (e) {
            e.preventDefault();
        }, { capture: true });

        document.addEventListener('auxclick', function (e) {
            if (e.button === 2) {
                e.preventDefault();
            }
        }, { capture: true });

        document.addEventListener('dragstart', function (e) {
            if (e.target instanceof HTMLImageElement) {
                e.preventDefault();
            }
        }, { capture: true });

        document.addEventListener('copy', function (e) {
            if (!allowClipboardFromField(e.target)) {
                e.preventDefault();
            }
        }, { capture: true });

        document.addEventListener('cut', function (e) {
            if (!allowClipboardFromField(e.target)) {
                e.preventDefault();
            }
        }, { capture: true });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'F12') {
                e.preventDefault();
                return;
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
            if ((e.ctrlKey || e.metaKey) && (e.key === 'p' || e.key === 'P')) {
                e.preventDefault();
            }
        }, { capture: true });

        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                document.body.classList.add('site-privacy-blur');
            } else {
                document.body.classList.remove('site-privacy-blur');
            }
        });

        window.addEventListener('blur', function () {
            document.body.classList.add('site-window-blur');
        });
        window.addEventListener('focus', function () {
            document.body.classList.remove('site-window-blur');
        });
    })();
</script>
