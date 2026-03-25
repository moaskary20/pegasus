{{-- منع قائمة النقر بالزر الأيمن على مستوى الصفحة (لا يمنع نسخ المحتوى أو أدوات المطوّر من المتصفح). --}}
<script>
    (function () {
        'use strict';
        function block(e) {
            e.preventDefault();
        }
        document.addEventListener('contextmenu', block, { capture: true });
        document.addEventListener('auxclick', function (e) {
            if (e.button === 2) {
                e.preventDefault();
            }
        }, { capture: true });
    })();
</script>
