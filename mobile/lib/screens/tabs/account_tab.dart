import 'package:flutter/material.dart';
import '../login_screen.dart';
import '../../api/auth_api.dart';
import '../widgets/app_header.dart';

const Color _primary = Color(0xFF2c004d);

/// تبويب حسابي
class AccountTab extends StatelessWidget {
  const AccountTab({super.key, this.onOpenDrawer});

  final VoidCallback? onOpenDrawer;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppHeader(
        title: 'حسابي',
        onMenu: onOpenDrawer ?? () => Scaffold.of(context).openDrawer(),
      ),
      body: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          const SizedBox(height: 16),
          Center(
            child: CircleAvatar(
              radius: 48,
              backgroundColor: _primary.withOpacity(0.2),
              child: Icon(Icons.person_rounded, size: 56, color: _primary),
            ),
          ),
          const SizedBox(height: 16),
          Center(
            child: Text(
              'المستخدم',
              style: Theme.of(context).textTheme.titleLarge?.copyWith(
                    fontWeight: FontWeight.bold,
                    color: _primary,
                  ),
            ),
          ),
          const SizedBox(height: 32),
          ListTile(
            leading: const Icon(Icons.person_outline, color: _primary),
            title: const Text('الملف الشخصي'),
            onTap: () {},
          ),
          ListTile(
            leading: const Icon(Icons.settings_outlined, color: _primary),
            title: const Text('الإعدادات'),
            onTap: () {},
          ),
          ListTile(
            leading: const Icon(Icons.help_outline, color: _primary),
            title: const Text('المساعدة'),
            onTap: () {},
          ),
          const Divider(height: 32),
          ListTile(
            leading: const Icon(Icons.logout_rounded, color: Colors.red),
            title: Text(
              'تسجيل الخروج',
              style: TextStyle(
                fontWeight: FontWeight.w600,
                color: Colors.red.shade700,
              ),
            ),
            onTap: () async {
              await AuthApi.logout();
              if (!context.mounted) return;
              Navigator.of(context).pushAndRemoveUntil(
                MaterialPageRoute(builder: (_) => const LoginScreen()),
                (route) => false,
              );
            },
          ),
        ],
      ),
    );
  }
}
