import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import '../login_screen.dart';
import '../../api/auth_api.dart';
import '../my_courses_screen.dart';
import '../my_assignments_screen.dart';
import '../cart_screen.dart';
import '../wishlist_screen.dart';
import '../notifications_reminders_screen.dart';
import '../messages_screen.dart';
import '../account_settings_screen.dart';
import '../subscriptions_screen.dart';
import '../purchase_history_screen.dart';
import '../support_screen.dart';
import '../instructor_finances_screen.dart';

const Color _primary = Color(0xFF2c004d);

/// محتوى السايدبار (قائمة + هيدر) — يجلب اسم المستخدم وصورته وإيميله من الـ backend
class AppDrawerContent extends StatefulWidget {
  const AppDrawerContent({super.key, this.onClose});

  final VoidCallback? onClose;

  @override
  State<AppDrawerContent> createState() => _AppDrawerContentState();
}

class _AppDrawerContentState extends State<AppDrawerContent> {
  Map<String, dynamic>? _user;
  bool _loadingUser = true;

  @override
  void initState() {
    super.initState();
    _loadUser();
  }

  Future<void> _loadUser() async {
    if (AuthApi.token == null) await AuthApi.loadStoredToken();
    final user = await AuthApi.getUser();
    if (mounted) {
      setState(() {
        _user = user;
        _loadingUser = false;
      });
    }
  }

  bool get _isInstructor {
    final roles = _user?['roles'];
    if (roles is List) {
      return roles.any((r) => r.toString().toLowerCase() == 'instructor');
    }
    return false;
  }

  @override
  Widget build(BuildContext context) {
    final name = _user?['name'] as String?;
    final email = _user?['email'] as String?;
    final avatarUrl = _user?['avatar_url'] as String?;
    return Column(
      children: [
        Expanded(
          child: ListView(
            padding: EdgeInsets.zero,
            children: [
              _UserHeader(
                userName: name ?? (_loadingUser ? '...' : 'المستخدم'),
                userEmail: email ?? (_loadingUser ? '' : ''),
                avatarUrl: avatarUrl,
                onEditTap: () => _openEditProfile(context),
              ),
              const Divider(height: 1),
              _DrawerTile(icon: Icons.school_rounded, label: 'تعلّمي / دوراتي', onTap: () => _push(context, const MyCoursesScreen())),
              _DrawerTile(icon: Icons.assignment_outlined, label: 'واجباتي', onTap: () => _push(context, const MyAssignmentsScreen())),
              _DrawerTile(icon: Icons.shopping_cart_outlined, label: 'سلة المشتريات', onTap: () => _push(context, const CartScreen())),
              _DrawerTile(icon: Icons.favorite_border_rounded, label: 'قائمة الرغبات', onTap: () => _push(context, const WishlistScreen())),
              _DrawerTile(icon: Icons.notifications_none_rounded, label: 'الإشعارات والتنبيهات', onTap: () => _push(context, const NotificationsRemindersScreen())),
              _DrawerTile(icon: Icons.chat_bubble_outline_rounded, label: 'الرسائل', onTap: () => _push(context, const MessagesScreen())),
              _DrawerTile(icon: Icons.settings_outlined, label: 'إعدادات الحساب', onTap: () => _push(context, const AccountSettingsScreen())),
              if (_isInstructor)
                _DrawerTile(icon: Icons.account_balance_wallet_outlined, label: 'الإدارة المالية', onTap: () => _push(context, const InstructorFinancesScreen())),
              _DrawerTile(icon: Icons.card_membership_outlined, label: 'الاشتراكات', onTap: () => _push(context, const SubscriptionsScreen())),
              _DrawerTile(icon: Icons.receipt_long_outlined, label: 'سجل المشتريات', onTap: () => _push(context, const PurchaseHistoryScreen())),
              _DrawerTile(icon: Icons.help_outline_rounded, label: 'المساعدة والدعم', onTap: () => _push(context, const SupportScreen())),
            ],
          ),
        ),
        const Divider(height: 1),
        _DrawerTile(
          icon: Icons.logout_rounded,
          label: 'تسجيل الخروج',
          onTap: () => _logout(context),
          color: Colors.red.shade700,
        ),
        SizedBox(height: MediaQuery.of(context).padding.bottom + 8),
      ],
    );
  }

  void _openEditProfile(BuildContext context) {
    widget.onClose?.call();
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => _EditProfileSheet(
        initialUser: _user,
        onSaved: () => _loadUser(),
      ),
    );
  }

  void _push(BuildContext context, Widget screen) {
    widget.onClose?.call();
    Navigator.of(context).push(
      MaterialPageRoute(builder: (_) => screen),
    );
  }

  Future<void> _logout(BuildContext context) async {
    widget.onClose?.call();
    await AuthApi.logout();
    if (!context.mounted) return;
    Navigator.of(context).pushAndRemoveUntil(
      MaterialPageRoute(builder: (_) => const LoginScreen()),
      (route) => false,
    );
  }
}

/// السايدبار الافتراضي (للتوافق مع Drawer)
class AppDrawer extends StatelessWidget {
  const AppDrawer({super.key});

  @override
  Widget build(BuildContext context) {
    return Drawer(
      child: AppDrawerContent(onClose: () => Navigator.pop(context)),
    );
  }
}

class _UserHeader extends StatelessWidget {
  const _UserHeader({
    required this.userName,
    required this.userEmail,
    this.avatarUrl,
    required this.onEditTap,
  });

  final String userName;
  final String userEmail;
  final String? avatarUrl;
  final VoidCallback onEditTap;

  Widget _avatarPlaceholder() {
    return Container(
      width: 88,
      height: 88,
      color: _primary.withValues(alpha: 0.2),
      child: Icon(Icons.person_rounded, size: 48, color: _primary),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: EdgeInsets.fromLTRB(20, MediaQuery.of(context).padding.top + 24, 20, 20),
      color: _primary.withValues(alpha: 0.08),
      child: Column(
        children: [
          GestureDetector(
            onTap: onEditTap,
            child: Stack(
              alignment: Alignment.center,
              children: [
                ClipOval(
                  child: avatarUrl != null && avatarUrl!.isNotEmpty
                      ? Image.network(
                          avatarUrl!,
                          width: 88,
                          height: 88,
                          fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) => _avatarPlaceholder(),
                        )
                      : _avatarPlaceholder(),
                ),
                Positioned(
                  bottom: 0,
                  left: 0,
                  child: Container(
                    padding: const EdgeInsets.all(6),
                    decoration: BoxDecoration(
                      color: _primary,
                      shape: BoxShape.circle,
                      border: Border.all(color: Colors.white, width: 2),
                    ),
                    child: const Icon(Icons.edit_rounded, size: 16, color: Colors.white),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 12),
          Text(
            userName,
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                  fontWeight: FontWeight.bold,
                  color: _primary,
                ),
          ),
          if (userEmail.isNotEmpty) ...[
            const SizedBox(height: 2),
            Text(
              userEmail,
              style: Theme.of(context).textTheme.bodySmall?.copyWith(
                    color: Colors.grey.shade600,
                  ),
            ),
          ],
        ],
      ),
    );
  }
}

class _DrawerTile extends StatelessWidget {
  const _DrawerTile({
    required this.icon,
    required this.label,
    required this.onTap,
    this.color,
  });

  final IconData icon;
  final String label;
  final VoidCallback onTap;
  final Color? color;

  @override
  Widget build(BuildContext context) {
    final c = color ?? _primary;
    return ListTile(
      leading: Icon(icon, color: c, size: 24),
      title: Text(
        label,
        style: TextStyle(
          fontWeight: FontWeight.w600,
          color: color ?? Colors.grey.shade800,
        ),
      ),
      onTap: onTap,
    );
  }
}

/// شيت تعديل الصورة والاسم والإيميل وكلمة المرور
class _EditProfileSheet extends StatefulWidget {
  const _EditProfileSheet({
    this.initialUser,
    this.onSaved,
  });

  final Map<String, dynamic>? initialUser;
  final VoidCallback? onSaved;

  @override
  State<_EditProfileSheet> createState() => _EditProfileSheetState();
}

class _EditProfileSheetState extends State<_EditProfileSheet> {
  late final TextEditingController _nameController;
  late final TextEditingController _emailController;
  late final TextEditingController _phoneController;
  File? _selectedImage;
  String? _avatarUrl;
  bool _saving = false;

  @override
  void initState() {
    super.initState();
    final user = widget.initialUser;
    _nameController = TextEditingController(text: user?['name']?.toString() ?? 'المستخدم');
    _emailController = TextEditingController(text: user?['email']?.toString() ?? '');
    _phoneController = TextEditingController(text: user?['phone']?.toString() ?? '');
    _avatarUrl = user?['avatar_url']?.toString();
  }

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    super.dispose();
  }

  Widget _avatarPlaceholder() {
    return Container(
      width: 96,
      height: 96,
      color: _primary.withValues(alpha: 0.2),
      child: Icon(Icons.person_rounded, size: 56, color: _primary),
    );
  }

  Future<void> _pickImage() async {
    try {
      final picker = ImagePicker();
      final x = await picker.pickImage(
        source: ImageSource.gallery,
        maxWidth: 512,
        maxHeight: 512,
        imageQuality: 85,
      );
      if (x != null && mounted) {
        setState(() => _selectedImage = File(x.path));
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('تعذر اختيار الصورة: $e'),
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    }
  }

  Future<void> _save() async {
    final name = _nameController.text.trim();
    final email = _emailController.text.trim();
    if (name.isEmpty || email.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('الاسم والبريد الإلكتروني مطلوبان'),
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }
    setState(() => _saving = true);
    final result = await AuthApi.updateProfile(
      name: name,
      email: email,
      phone: _phoneController.text.trim().isNotEmpty ? _phoneController.text.trim() : null,
      avatarFile: _selectedImage,
    );
    if (!mounted) return;
    setState(() => _saving = false);
    if (result.isSuccess) {
      widget.onSaved?.call();
      Navigator.of(context).pop();
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('تم تحديث البيانات بنجاح'),
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

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      padding: EdgeInsets.only(
        left: 24,
        right: 24,
        top: 24,
        bottom: MediaQuery.of(context).padding.bottom + 24,
      ),
      child: SingleChildScrollView(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Center(
              child: Container(
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: Colors.grey.shade300,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
            ),
            const SizedBox(height: 20),
            Text(
              'تعديل الملف الشخصي',
              style: Theme.of(context).textTheme.titleLarge?.copyWith(
                    fontWeight: FontWeight.bold,
                    color: _primary,
                  ),
            ),
            const SizedBox(height: 20),
            Center(
              child: Stack(
                children: [
                  GestureDetector(
                    onTap: _pickImage,
                    child: ClipOval(
                      child: SizedBox(
                        width: 96,
                        height: 96,
                        child: _selectedImage != null
                            ? Image.file(_selectedImage!, fit: BoxFit.cover)
                            : (_avatarUrl != null && _avatarUrl!.isNotEmpty
                                ? Image.network(
                                    _avatarUrl!,
                                    fit: BoxFit.cover,
                                    errorBuilder: (_, __, ___) => _avatarPlaceholder(),
                                  )
                                : _avatarPlaceholder()),
                      ),
                    ),
                  ),
                  Positioned(
                    bottom: 0,
                    left: 0,
                    child: GestureDetector(
                      onTap: _pickImage,
                      child: Container(
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          color: _primary,
                          shape: BoxShape.circle,
                          border: Border.all(color: Colors.white, width: 2),
                        ),
                        child: const Icon(Icons.camera_alt_rounded, size: 20, color: Colors.white),
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),
            TextFormField(
              controller: _nameController,
              decoration: _inputDecoration('الاسم'),
              textDirection: TextDirection.rtl,
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _emailController,
              keyboardType: TextInputType.emailAddress,
              decoration: _inputDecoration('البريد الإلكتروني'),
              textDirection: TextDirection.ltr,
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _phoneController,
              keyboardType: TextInputType.phone,
              decoration: _inputDecoration('رقم الهاتف'),
              textDirection: TextDirection.ltr,
            ),
            const SizedBox(height: 24),
            FilledButton(
              onPressed: _saving ? null : _save,
              style: FilledButton.styleFrom(
                backgroundColor: _primary,
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
              child: _saving
                  ? const SizedBox(
                      height: 20,
                      width: 20,
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        color: Colors.white,
                      ),
                    )
                  : const Text('حفظ التغييرات'),
            ),
          ],
        ),
      ),
    );
  }

  InputDecoration _inputDecoration(String label) {
    return InputDecoration(
      labelText: label,
      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: _primary, width: 2),
      ),
    );
  }
}
