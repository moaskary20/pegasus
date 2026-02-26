import 'package:flutter/material.dart';
import '../app_theme.dart';
import '../api/auth_api.dart';
import 'feature_scaffold.dart';

/// شاشة تغيير كلمة المرور
class PasswordChangeScreen extends StatefulWidget {
  const PasswordChangeScreen({super.key});

  @override
  State<PasswordChangeScreen> createState() => _PasswordChangeScreenState();
}

class _PasswordChangeScreenState extends State<PasswordChangeScreen> {
  final _currentController = TextEditingController();
  final _newController = TextEditingController();
  final _confirmController = TextEditingController();
  bool _obscureCurrent = true;
  bool _obscureNew = true;
  bool _obscureConfirm = true;
  bool _saving = false;

  @override
  void dispose() {
    _currentController.dispose();
    _newController.dispose();
    _confirmController.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    final current = _currentController.text;
    final newPass = _newController.text;
    final confirm = _confirmController.text;

    if (current.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('أدخل كلمة المرور الحالية'),
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }
    if (newPass.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('أدخل كلمة المرور الجديدة'),
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }
    if (newPass.length < 8) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('كلمة المرور الجديدة يجب أن تكون 8 أحرف على الأقل'),
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }
    if (newPass != confirm) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('كلمة المرور الجديدة وتأكيدها غير متطابقتين'),
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    setState(() => _saving = true);
    final result = await AuthApi.updatePassword(
      currentPassword: current,
      newPassword: newPass,
    );
    if (!mounted) return;
    setState(() => _saving = false);
    if (result.isSuccess) {
      _currentController.clear();
      _newController.clear();
      _confirmController.clear();
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('تم تغيير كلمة المرور بنجاح'),
          behavior: SnackBarBehavior.floating,
        ),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(result.message ?? 'حدث خطأ'),
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  InputDecoration _decoration(String label, bool obscure, VoidCallback onToggle) {
    return InputDecoration(
      labelText: label,
      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: AppTheme.primary, width: 2),
      ),
      suffixIcon: IconButton(
        icon: Icon(obscure ? Icons.visibility_off : Icons.visibility),
        onPressed: onToggle,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return FeatureScaffold(
      title: 'تغيير كلمة المرور',
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            TextFormField(
              controller: _currentController,
              obscureText: _obscureCurrent,
              textDirection: TextDirection.ltr,
              decoration: _decoration(
                'كلمة المرور الحالية',
                _obscureCurrent,
                () => setState(() => _obscureCurrent = !_obscureCurrent),
              ),
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _newController,
              obscureText: _obscureNew,
              textDirection: TextDirection.ltr,
              decoration: _decoration(
                'كلمة المرور الجديدة',
                _obscureNew,
                () => setState(() => _obscureNew = !_obscureNew),
              ),
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _confirmController,
              obscureText: _obscureConfirm,
              textDirection: TextDirection.ltr,
              decoration: _decoration(
                'تأكيد كلمة المرور الجديدة',
                _obscureConfirm,
                () => setState(() => _obscureConfirm = !_obscureConfirm),
              ),
            ),
            const SizedBox(height: 24),
            Text(
              'يجب أن تكون كلمة المرور 8 أحرف على الأقل',
              style: Theme.of(context).textTheme.bodySmall?.copyWith(color: Colors.grey.shade600),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 24),
            FilledButton(
              onPressed: _saving ? null : _save,
              style: FilledButton.styleFrom(
                backgroundColor: AppTheme.primary,
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
              child: _saving
                  ? const SizedBox(
                      height: 22,
                      width: 22,
                      child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                    )
                  : const Text('تغيير كلمة المرور'),
            ),
          ],
        ),
      ),
    );
  }
}
