@extends('layouts.site')

@section('title', 'Privacy Policy — ' . config('app.name', 'Pegasus Academy'))

@section('content')
<div class="bg-slate-50 min-h-[60vh]" lang="en" dir="ltr">
    <div class="max-w-3xl mx-auto px-4 py-12 md:py-16 text-left">
        <p class="text-xs font-semibold uppercase tracking-wider text-[#2c004d] mb-2">Legal</p>
        <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900">Privacy Policy</h1>
        <p class="mt-2 text-sm text-slate-600">Last updated: {{ now()->format('F j, Y') }}</p>

        <div class="mt-10 prose prose-slate prose-headings:font-bold prose-a:text-[#2c004d] max-w-none text-slate-700 leading-relaxed">
            <p>
                {{ config('app.name', 'Pegasus Academy') }} (“we”, “us”, or “our”) operates the website and mobile application (the “Service”). This Privacy Policy describes how we collect, use, disclose, and safeguard your information when you use our Service. By using the Service, you agree to the collection and use of information in accordance with this policy.
            </p>

            <h2 class="text-xl font-extrabold text-slate-900 mt-10">1. Information we collect</h2>
            <p>We may collect the following types of information:</p>
            <ul class="list-disc ps-5 space-y-2">
                <li><strong>Account information:</strong> name, email address, phone number (if provided), and password (stored securely).</li>
                <li><strong>Profile and usage:</strong> courses you enroll in, progress, quiz results, messages you send through the platform, and preferences.</li>
                <li><strong>Device and technical data:</strong> device type, operating system, app version, approximate language, and diagnostic data to improve stability and security.</li>
                <li><strong>Payments:</strong> when you purchase courses or products, payment processing is handled by third-party payment providers. We do not store full payment card numbers on our servers.</li>
                <li><strong>Communications:</strong> content you submit through contact forms, support requests, or in-app messaging.</li>
            </ul>

            <h2 class="text-xl font-extrabold text-slate-900 mt-10">2. How we use your information</h2>
            <p>We use the information we collect to:</p>
            <ul class="list-disc ps-5 space-y-2">
                <li>Provide, maintain, and improve the Service;</li>
                <li>Create and manage your account and process enrollments and orders;</li>
                <li>Send transactional notifications (e.g., account, purchases, security alerts);</li>
                <li>Respond to your requests and provide customer support;</li>
                <li>Analyze usage in aggregate to improve content and user experience;</li>
                <li>Detect, prevent, and address fraud, abuse, or technical issues;</li>
                <li>Comply with legal obligations.</li>
            </ul>

            <h2 class="text-xl font-extrabold text-slate-900 mt-10">3. Legal bases (where applicable)</h2>
            <p>Depending on your region, we may rely on: performance of a contract, legitimate interests (e.g., security and analytics), consent (where required), or legal obligation.</p>

            <h2 class="text-xl font-extrabold text-slate-900 mt-10">4. Sharing of information</h2>
            <p>We do not sell your personal information. We may share information with:</p>
            <ul class="list-disc ps-5 space-y-2">
                <li><strong>Service providers</strong> who assist us (hosting, analytics, email delivery, payment processing), bound by confidentiality and data-processing terms;</li>
                <li><strong>Legal authorities</strong> when required by law or to protect rights, safety, and security;</li>
                <li><strong>Business transfers</strong> in connection with a merger, acquisition, or sale of assets, subject to appropriate safeguards.</li>
            </ul>

            <h2 class="text-xl font-extrabold text-slate-900 mt-10">5. Data retention</h2>
            <p>We retain personal information only as long as necessary to fulfill the purposes described in this policy, unless a longer retention period is required or permitted by law.</p>

            <h2 class="text-xl font-extrabold text-slate-900 mt-10">6. Security</h2>
            <p>We implement appropriate technical and organizational measures to protect your information. No method of transmission over the Internet or electronic storage is 100% secure; we strive to use commercially acceptable means to protect your data.</p>

            <h2 class="text-xl font-extrabold text-slate-900 mt-10">7. Your rights</h2>
            <p>Depending on your location, you may have the right to access, correct, delete, or export your personal data, restrict or object to certain processing, or withdraw consent where processing is based on consent. To exercise these rights, contact us using the details below.</p>

            <h2 class="text-xl font-extrabold text-slate-900 mt-10">8. Children’s privacy</h2>
            <p>Our Service is not directed to children under 13 (or the minimum age required in your jurisdiction). We do not knowingly collect personal information from children. If you believe we have collected such information, please contact us and we will take steps to delete it.</p>

            <h2 class="text-xl font-extrabold text-slate-900 mt-10">9. International transfers</h2>
            <p>Your information may be processed in countries other than your own. Where required, we implement appropriate safeguards for such transfers.</p>

            <h2 class="text-xl font-extrabold text-slate-900 mt-10">10. Third-party services</h2>
            <p>The Service may contain links to third-party websites or integrate third-party SDKs (e.g., analytics, payments). Their privacy practices are governed by their own policies. We encourage you to read them.</p>

            <h2 class="text-xl font-extrabold text-slate-900 mt-10">11. Changes to this policy</h2>
            <p>We may update this Privacy Policy from time to time. We will post the new version on this page and update the “Last updated” date. Continued use of the Service after changes constitutes acceptance of the updated policy, where permitted by law.</p>

            <h2 class="text-xl font-extrabold text-slate-900 mt-10">12. Contact us</h2>
            <p>
                If you have questions about this Privacy Policy or our data practices, please contact us:
            </p>
            <ul class="list-none ps-0 space-y-1 mt-2">
                <li><strong>App / organization:</strong> {{ config('app.name', 'Pegasus Academy') }}</li>
                <li><strong>Website:</strong> <a href="{{ url('/') }}" class="underline">{{ url('/') }}</a></li>
                <li><strong>Contact page:</strong> <a href="{{ route('site.contact') }}" class="underline">{{ route('site.contact') }}</a></li>
            </ul>
            <p class="mt-6 text-sm text-slate-500">
                For Google Play: you may use this page as the public Privacy Policy URL for your app listing.
            </p>
        </div>
    </div>
</div>
@endsection
