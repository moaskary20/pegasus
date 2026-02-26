# Pegasus Academy - تطبيق الموبايل

تطبيق Flutter لتطبيقPegasus Academy على Android و iOS.

## المتطلبات

- [Flutter SDK](https://docs.flutter.dev/get-started/install)
- Dart 3.10+

## التشغيل

```bash
cd mobile
flutter pub get
flutter run
```

## البناء

- **Android:** `flutter build apk` أو `flutter build appbundle`
- **iOS:** `flutter build ios`

## هيكل المشروع

- `lib/main.dart` — نقطة الدخول
- `lib/` — كود التطبيق
- `android/` — إعدادات Android
- `ios/` — إعدادات iOS

## ربط التطبيق بالـ API

لربط التطبيق بالـ API الخاص بالمنصة (Laravel)، يمكنك:

1. إضافة حزمة `http` أو `dio` في `pubspec.yaml`
2. إنشاء مجلد `lib/api/` لطلبات الـ API
3. استخدام عنوان الـ API الأساسي (مثل `https://academypegasus.com/api`)
