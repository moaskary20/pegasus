import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import '../app_theme.dart';
import '../api/auth_api.dart';
import 'feature_scaffold.dart';

/// شاشة تعديل الملف الشخصي — اسم، بريد، هاتف، مدينة، عمل، صورة
class ProfileEditScreen extends StatefulWidget {
  const ProfileEditScreen({super.key});

  @override
  State<ProfileEditScreen> createState() => _ProfileEditScreenState();
}

class _ProfileEditScreenState extends State<ProfileEditScreen> {
  Map<String, dynamic>? _user;
  bool _loading = true;
  bool _saving = false;

  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  final _cityController = TextEditingController();
  final _jobController = TextEditingController();
  File? _selectedImage;
  String? _avatarUrl;

  @override
  void initState() {
    super.initState();
    _loadUser();
  }

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _cityController.dispose();
    _jobController.dispose();
    super.dispose();
  }

  Future<void> _loadUser() async {
    setState(() => _loading = true);
    final user = await AuthApi.getUser();
    if (mounted) {
      setState(() {
        _user = user;
        _loading = false;
        if (user != null) {
          _nameController.text = user['name']?.toString() ?? '';
          _emailController.text = user['email']?.toString() ?? '';
          _phoneController.text = user['phone']?.toString() ?? '';
          _cityController.text = user['city']?.toString() ?? '';
          _jobController.text = user['job']?.toString() ?? '';
          _avatarUrl = user['avatar_url']?.toString();
        }
      });
    }
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
      city: _cityController.text.trim().isNotEmpty ? _cityController.text.trim() : null,
      job: _jobController.text.trim().isNotEmpty ? _jobController.text.trim() : null,
      avatarFile: _selectedImage,
    );
    if (!mounted) return;
    setState(() => _saving = false);
    if (result.isSuccess) {
      setState(() {
        _user = result.user ?? _user;
        _selectedImage = null;
        if (result.user != null) {
          _avatarUrl = result.user!['avatar_url']?.toString();
        }
      });
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

  Widget _buildAvatar() {
    return GestureDetector(
      onTap: _pickImage,
      child: Stack(
        alignment: Alignment.center,
        children: [
          ClipOval(
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
          Positioned(
            bottom: 0,
            left: 0,
            child: Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: AppTheme.primary,
                shape: BoxShape.circle,
                border: Border.all(color: Colors.white, width: 2),
              ),
              child: const Icon(Icons.camera_alt_rounded, size: 20, color: Colors.white),
            ),
          ),
        ],
      ),
    );
  }

  Widget _avatarPlaceholder() {
    return Container(
      width: 96,
      height: 96,
      color: AppTheme.primary.withValues(alpha: 0.2),
      child: Icon(Icons.person_rounded, size: 56, color: AppTheme.primary),
    );
  }

  @override
  Widget build(BuildContext context) {
    return FeatureScaffold(
      title: 'الملف الشخصي',
      body: _loading
          ? const Center(child: Padding(
              padding: EdgeInsets.all(48),
              child: CircularProgressIndicator(color: AppTheme.primary),
            ))
          : RefreshIndicator(
              onRefresh: _loadUser,
              color: AppTheme.primary,
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.all(20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Center(child: _buildAvatar()),
                    const SizedBox(height: 24),
                    _buildField('الاسم', _nameController, TextInputType.text, TextDirection.rtl),
                    const SizedBox(height: 16),
                    _buildField('البريد الإلكتروني', _emailController, TextInputType.emailAddress, TextDirection.ltr),
                    const SizedBox(height: 16),
                    _buildField('رقم الهاتف', _phoneController, TextInputType.phone, TextDirection.ltr),
                    const SizedBox(height: 16),
                    _buildField('المدينة', _cityController, TextInputType.text, TextDirection.rtl),
                    const SizedBox(height: 16),
                    _buildField('المهنة', _jobController, TextInputType.text, TextDirection.rtl),
                    const SizedBox(height: 32),
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
                          : const Text('حفظ التغييرات'),
                    ),
                  ],
                ),
              ),
            ),
    );
  }

  Widget _buildField(String label, TextEditingController controller, TextInputType type, TextDirection dir) {
    return TextFormField(
      controller: controller,
      keyboardType: type,
      textDirection: dir,
      decoration: InputDecoration(
        labelText: label,
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: AppTheme.primary, width: 2),
        ),
      ),
    );
  }
}
