import 'package:flutter/material.dart';

/// الصفحة الرئيسية بعد تسجيل الدخول (مؤقتة)
class HomePage extends StatelessWidget {
  const HomePage({super.key, required this.title});

  final String title;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        backgroundColor: const Color(0xFF2c004d),
        foregroundColor: Colors.white,
        title: Text(title),
      ),
      body: const Center(
        child: Text('مرحباً بك في أكاديمية بيغاسوس'),
      ),
    );
  }
}
