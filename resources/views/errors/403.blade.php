<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>غير مسموح</title>
    <style>
        :root { color-scheme: light; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: "Tajawal", "Segoe UI", Tahoma, sans-serif;
            background: linear-gradient(160deg, #f8f5fb 0%, #efe6f7 45%, #f5f5f5 100%);
            color: #1f1230;
            padding: 24px;
        }
        .card {
            width: 100%;
            max-width: 440px;
            background: #fff;
            border: 1px solid #eadff3;
            border-radius: 24px;
            padding: 36px 28px;
            text-align: center;
            box-shadow: 0 18px 50px rgba(61, 25, 92, 0.08);
        }
        .badge {
            width: 72px;
            height: 72px;
            margin: 0 auto 18px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: #fde8e8;
            color: #b42318;
            font-size: 34px;
            font-weight: 800;
        }
        h1 {
            margin: 0 0 10px;
            font-size: 1.75rem;
            color: #3d195c;
        }
        p {
            margin: 0 0 24px;
            line-height: 1.8;
            color: #5b5266;
            font-size: 1rem;
        }
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }
        a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 0 18px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.95rem;
        }
        .primary {
            background: #3d195c;
            color: #fff;
        }
        .secondary {
            background: #f3eef8;
            color: #3d195c;
        }
    </style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="card">
        <div class="badge" aria-hidden="true">!</div>
        <h1>غير مسموح</h1>
        <p>
            @if(request()->is('admin') || request()->is('admin/*'))
                لا يمكن للطلاب الدخول إلى لوحة التحكم. هذه المنطقة مخصصة للإدارة والمدربين فقط.
            @else
                {{ $exception->getMessage() ?: 'ليس لديك صلاحية للوصول إلى هذه الصفحة.' }}
            @endif
        </p>
        <div class="actions">
            <a class="primary" href="{{ url('/') }}">العودة للرئيسية</a>
            @auth
                <a class="secondary" href="{{ route('site.my-courses') }}">دوراتي</a>
            @else
                <a class="secondary" href="{{ route('site.auth') }}">تسجيل الدخول</a>
            @endauth
        </div>
    </div>
</body>
</html>
