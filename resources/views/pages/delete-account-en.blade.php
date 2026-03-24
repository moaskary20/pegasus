@extends('layouts.site')

@section('title', 'Delete account — ' . config('app.name', 'Pegasus Academy'))

@section('content')
<div class="bg-slate-50 min-h-[60vh]" lang="en" dir="ltr">
    <div class="max-w-3xl mx-auto px-4 py-12 md:py-16 text-left">
        <p class="text-xs font-semibold uppercase tracking-wider text-[#2c004d] mb-2">Account</p>
        <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900">Delete your account</h1>
        <p class="mt-2 text-sm text-slate-600">Last updated: {{ now()->format('F j, Y') }}</p>

        <div class="mt-10 prose prose-slate prose-headings:font-bold prose-a:text-[#2c004d] max-w-none text-slate-700 leading-relaxed">
            <p>
                You can request deletion of your {{ config('app.name', 'Pegasus Academy') }} account and associated personal data. This page explains how to do it on the website and how to contact us if you need help (for example, from the mobile app or if you cannot sign in).
            </p>

            <h2 class="text-xl font-extrabold text-slate-900 mt-10">1. Delete account from the website</h2>
            <p>If you can sign in:</p>
            <ol class="list-decimal ps-5 space-y-2">
                <li>Log in to your account.</li>
                <li>Open <strong>Account</strong> / profile settings.</li>
                <li>Find the <strong>Delete account</strong> section and follow the steps to confirm permanent deletion.</li>
            </ol>
            <p class="mt-4">
                <a href="{{ route('site.account') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#2c004d] text-white font-bold text-sm hover:bg-[#2c004d]/90 transition">Go to account settings</a>
                @guest
                    <span class="block mt-2 text-sm text-slate-500">You may need to <a href="{{ route('site.auth') }}" class="underline font-semibold">sign in</a> first.</span>
                @endguest
            </p>

            <h2 class="text-xl font-extrabold text-slate-900 mt-10">2. Delete account from the mobile app</h2>
            <p>
                If you use our mobile app, use the in-app account or profile screen to request account deletion when that option is available. If you do not see it, use the contact method below and include the email address registered on your account.
            </p>

            <h2 class="text-xl font-extrabold text-slate-900 mt-10">3. Request deletion by email or contact form</h2>
            <p>
                If you cannot access your account, send a request from the email address associated with your account (if possible) so we can verify ownership. Include the subject line: <strong>Account deletion request</strong> and your registered name or email.
            </p>
            <ul class="list-none ps-0 space-y-2 mt-3">
                <li><a href="{{ route('site.contact') }}" class="underline font-semibold">Contact us (web form)</a></li>
                <li><a href="{{ route('site.support') }}" class="underline font-semibold">Support</a></li>
            </ul>

            <h2 class="text-xl font-extrabold text-slate-900 mt-10">4. What happens when your account is deleted</h2>
            <p>After deletion is completed (subject to legal or billing retention requirements):</p>
            <ul class="list-disc ps-5 space-y-2">
                <li>Your profile and login credentials are removed or anonymized.</li>
                <li>You may lose access to enrolled courses, progress, certificates, orders, and messages tied to that account.</li>
                <li>Some information may be retained where required by law (e.g. tax or accounting records) or for legitimate security and fraud-prevention purposes, in aggregated or minimized form where possible.</li>
            </ul>

            <h2 class="text-xl font-extrabold text-slate-900 mt-10">5. Processing time</h2>
            <p>
                We aim to process verified deletion requests within a reasonable period (typically within 30 days), unless a longer period is required by law or for dispute resolution.
            </p>

            <h2 class="text-xl font-extrabold text-slate-900 mt-10">6. Questions</h2>
            <p>
                For questions about this process, use our <a href="{{ route('site.contact') }}" class="underline">contact page</a> or <a href="{{ route('site.privacy-policy') }}" class="underline">Privacy Policy</a>.
            </p>

            {{-- Arabic summary for local users --}}
            <div class="mt-12 pt-8 border-t border-slate-200" lang="ar" dir="rtl">
                <h2 class="text-lg font-extrabold text-slate-900">ملخص بالعربية</h2>
                <p class="text-slate-700 mt-2 text-sm leading-relaxed">
                    لحذف حسابك: سجّل الدخول ثم افتح <a href="{{ route('site.account') }}" class="text-[#2c004d] font-bold underline">إعدادات الحساب</a> واستخدم قسم حذف الحساب. إذا لم تتمكن من الدخول، راسلنا عبر <a href="{{ route('site.contact') }}" class="text-[#2c004d] font-bold underline">صفحة الاتصال</a> أو <a href="{{ route('site.support') }}" class="text-[#2c004d] font-bold underline">الدعم</a> مع ذكر «طلب حذف الحساب» والبريد المسجّل.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
