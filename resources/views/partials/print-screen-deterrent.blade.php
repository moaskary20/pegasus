{{--
    تثبيط مفتاح Print Screen واختصارات لقطة شاشة شائعة في المتصفح.
    لا يمكن منع لقطات النظام (Win+Shift+S) أو أدوات خارج المتصفح — حدّ أقصى ما يسمح به الويب.
--}}
<script>
    (function () {
        'use strict';

        function isPrintScreenKey(e) {
            return e.key === 'PrintScreen'
                || e.code === 'PrintScreen'
                || e.keyCode === 44
                || e.which === 44;
        }

        var lastWarn = 0;
        function warnPrintScreen() {
            var now = Date.now();
            if (now - lastWarn < 800) {
                return;
            }
            lastWarn = now;
            try {
                alert('ممنوع استخدام طباعة الشاشة (Print Screen) أو اختصار لقطة الشاشة في هذا الموقع.');
            } catch (err) {}
        }

        function handleKey(e) {
            if (!isPrintScreenKey(e)) {
                return;
            }
            e.preventDefault();
            e.stopPropagation();
            if (e.type === 'keyup') {
                warnPrintScreen();
            }
        }

        window.addEventListener('keydown', handleKey, true);
        window.addEventListener('keyup', handleKey, true);
    })();
</script>
