import 'package:flutter/material.dart';
import '../app_theme.dart';
import 'notifications_screen.dart';
import 'reminders_screen.dart';

/// شاشة موحدة: الإشعارات + التنبيهات في تبويبات
class NotificationsRemindersScreen extends StatelessWidget {
  const NotificationsRemindersScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: 2,
      child: Scaffold(
        appBar: AppBar(
          backgroundColor: AppTheme.primary,
          foregroundColor: Colors.white,
          elevation: 0,
          title: const Text('الإشعارات والتنبيهات'),
          bottom: TabBar(
            indicatorColor: Colors.white,
            indicatorWeight: 3,
            labelColor: Colors.white,
            unselectedLabelColor: Colors.white70,
            tabs: const [
              Tab(text: 'الإشعارات'),
              Tab(text: 'التنبيهات'),
            ],
          ),
        ),
        body: const TabBarView(
          children: [
            NotificationsScreen(embedded: true),
            RemindersScreen(embedded: true),
          ],
        ),
      ),
    );
  }
}
