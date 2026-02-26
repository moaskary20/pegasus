import 'package:flutter/material.dart';
import '../app_theme.dart';
import '../api/support_api.dart';
import 'feature_scaffold.dart';

/// المساعدة والدعم — بيانات وإرسال من الـ backend (GET /api/support، POST complaint/contact)
class SupportScreen extends StatefulWidget {
  const SupportScreen({super.key});

  @override
  State<SupportScreen> createState() => _SupportScreenState();
}

class _SupportScreenState extends State<SupportScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  SupportSettingsResponse? _settings;
  bool _loadingSettings = true;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    _loadSettings();
  }

  Future<void> _loadSettings() async {
    final res = await SupportApi.getSettings();
    if (mounted) setState(() { _settings = res; _loadingSettings = false; });
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return FeatureScaffold(
      title: 'المساعدة والدعم',
      body: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20),
            child: TweenAnimationBuilder<double>(
              tween: AppTheme.fadeInTween(),
              duration: AppTheme.animNormal,
              builder: (context, value, _) => Opacity(
                opacity: value,
                child: Text(
                  'تقديم شكوى أو استفسار',
                  style: Theme.of(context).textTheme.bodyLarge?.copyWith(color: Colors.grey.shade600),
                ),
              ),
            ),
          ),
          const SizedBox(height: 16),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20),
            child: TweenAnimationBuilder<double>(
              tween: AppTheme.fadeInTween(),
              duration: AppTheme.animNormal,
              builder: (context, value, _) => Opacity(
                opacity: value,
                child: Container(
                  decoration: BoxDecoration(
                    color: AppTheme.primary.withValues(alpha: 0.08),
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: TabBar(
                    controller: _tabController,
                    indicator: BoxDecoration(
                      color: AppTheme.primary,
                      borderRadius: BorderRadius.circular(14),
                    ),
                    indicatorSize: TabBarIndicatorSize.tab,
                    labelColor: Colors.white,
                    unselectedLabelColor: AppTheme.primaryDark,
                    labelStyle: const TextStyle(fontWeight: FontWeight.bold),
                    tabs: const [
                      Tab(text: 'شكوى'),
                      Tab(text: 'تواصل'),
                    ],
                  ),
                ),
              ),
            ),
          ),
          const SizedBox(height: 24),
          Expanded(
            child: TabBarView(
              controller: _tabController,
              children: [
                _ComplaintForm(),
                _ContactForm(),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _ComplaintForm extends StatefulWidget {
  @override
  State<_ComplaintForm> createState() => _ComplaintFormState();
}

class _ComplaintFormState extends State<_ComplaintForm> {
  final _formKey = GlobalKey<FormState>();
  final _name = TextEditingController();
  final _email = TextEditingController();
  final _phone = TextEditingController();
  final _subject = TextEditingController();
  final _message = TextEditingController();
  bool _sending = false;

  @override
  void dispose() {
    _name.dispose();
    _email.dispose();
    _phone.dispose();
    _subject.dispose();
    _message.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _sending = true);
    final result = await SupportApi.submitComplaint(
      name: _name.text.trim(),
      email: _email.text.trim(),
      phone: _phone.text.trim().isEmpty ? null : _phone.text.trim(),
      subject: _subject.text.trim(),
      message: _message.text.trim(),
    );
    if (!mounted) return;
    setState(() => _sending = false);
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(result.message),
        backgroundColor: result.success ? AppTheme.primary : Colors.red.shade700,
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Form(
        key: _formKey,
        child: Column(
          children: [
            _AnimatedField(
              index: 0,
              controller: _name,
              label: 'الاسم',
              icon: Icons.person_outline_rounded,
              validator: (v) => v == null || v.trim().isEmpty ? 'الاسم مطلوب' : null,
            ),
            _AnimatedField(
              index: 1,
              controller: _email,
              label: 'البريد الإلكتروني',
              icon: Icons.email_outlined,
              keyboardType: TextInputType.emailAddress,
              validator: (v) {
                if (v == null || v.trim().isEmpty) return 'البريد مطلوب';
                if (!v.contains('@')) return 'أدخل بريداً صحيحاً';
                return null;
              },
            ),
            _AnimatedField(
              index: 2,
              controller: _phone,
              label: 'رقم الهاتف (اختياري)',
              icon: Icons.phone_outlined,
              keyboardType: TextInputType.phone,
            ),
            _AnimatedField(
              index: 3,
              controller: _subject,
              label: 'موضوع الشكوى',
              icon: Icons.subject_rounded,
              validator: (v) => v == null || v.trim().isEmpty ? 'الموضوع مطلوب' : null,
            ),
            _AnimatedField(
              index: 4,
              controller: _message,
              label: 'تفاصيل الشكوى',
              icon: Icons.message_outlined,
              maxLines: 4,
              validator: (v) => v == null || v.trim().isEmpty ? 'التفاصيل مطلوبة' : null,
            ),
            const SizedBox(height: 24),
            TweenAnimationBuilder<double>(
              tween: AppTheme.scaleInTween(),
              duration: AppTheme.animNormal,
              curve: AppTheme.curveEmphasized,
              builder: (context, value, _) => Transform.scale(
                scale: value,
                child: SizedBox(
                  width: double.infinity,
                  child: FilledButton(
                    onPressed: _sending ? null : _submit,
                    style: FilledButton.styleFrom(
                      backgroundColor: AppTheme.primary,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                    child: _sending
                        ? const SizedBox(
                            height: 24,
                            width: 24,
                            child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                          )
                        : const Text('إرسال الشكوى'),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _ContactForm extends StatefulWidget {
  @override
  State<_ContactForm> createState() => _ContactFormState();
}

class _ContactFormState extends State<_ContactForm> {
  final _formKey = GlobalKey<FormState>();
  final _name = TextEditingController();
  final _email = TextEditingController();
  final _phone = TextEditingController();
  final _subject = TextEditingController();
  final _message = TextEditingController();
  bool _sending = false;

  @override
  void dispose() {
    _name.dispose();
    _email.dispose();
    _phone.dispose();
    _subject.dispose();
    _message.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _sending = true);
    final result = await SupportApi.submitContact(
      name: _name.text.trim(),
      email: _email.text.trim(),
      phone: _phone.text.trim().isEmpty ? null : _phone.text.trim(),
      subject: _subject.text.trim(),
      message: _message.text.trim(),
    );
    if (!mounted) return;
    setState(() => _sending = false);
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(result.message),
        backgroundColor: result.success ? AppTheme.primary : Colors.red.shade700,
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Form(
        key: _formKey,
        child: Column(
          children: [
            _AnimatedField(
              index: 0,
              controller: _name,
              label: 'الاسم',
              icon: Icons.person_outline_rounded,
              validator: (v) => v == null || v.trim().isEmpty ? 'الاسم مطلوب' : null,
            ),
            _AnimatedField(
              index: 1,
              controller: _email,
              label: 'البريد الإلكتروني',
              icon: Icons.email_outlined,
              keyboardType: TextInputType.emailAddress,
              validator: (v) {
                if (v == null || v.trim().isEmpty) return 'البريد مطلوب';
                if (!v.contains('@')) return 'أدخل بريداً صحيحاً';
                return null;
              },
            ),
            _AnimatedField(
              index: 2,
              controller: _phone,
              label: 'رقم الهاتف (اختياري)',
              icon: Icons.phone_outlined,
              keyboardType: TextInputType.phone,
            ),
            _AnimatedField(
              index: 3,
              controller: _subject,
              label: 'الموضوع',
              icon: Icons.subject_rounded,
              validator: (v) => v == null || v.trim().isEmpty ? 'الموضوع مطلوب' : null,
            ),
            _AnimatedField(
              index: 4,
              controller: _message,
              label: 'الرسالة',
              icon: Icons.message_outlined,
              maxLines: 4,
              validator: (v) => v == null || v.trim().isEmpty ? 'الرسالة مطلوبة' : null,
            ),
            const SizedBox(height: 24),
            TweenAnimationBuilder<double>(
              tween: AppTheme.scaleInTween(),
              duration: AppTheme.animNormal,
              curve: AppTheme.curveEmphasized,
              builder: (context, value, _) => Transform.scale(
                scale: value,
                child: SizedBox(
                  width: double.infinity,
                  child: FilledButton(
                    onPressed: _sending ? null : _submit,
                    style: FilledButton.styleFrom(
                      backgroundColor: AppTheme.primary,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                    child: _sending
                        ? const SizedBox(
                            height: 24,
                            width: 24,
                            child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                          )
                        : const Text('إرسال الرسالة'),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _AnimatedField extends StatelessWidget {
  const _AnimatedField({
    required this.index,
    required this.controller,
    required this.label,
    required this.icon,
    this.keyboardType,
    this.maxLines = 1,
    this.validator,
  });

  final int index;
  final TextEditingController controller;
  final String label;
  final IconData icon;
  final TextInputType? keyboardType;
  final int maxLines;
  final String? Function(String?)? validator;

  @override
  Widget build(BuildContext context) {
    return TweenAnimationBuilder<double>(
      tween: AppTheme.fadeInTween(),
      duration: Duration(milliseconds: 260 + (index * 40)),
      curve: AppTheme.curveDefault,
      builder: (context, value, _) => Opacity(
        opacity: value,
        child: Transform.translate(
          offset: Offset(0, 12 * (1 - value)),
          child: Padding(
            padding: const EdgeInsets.only(bottom: 16),
            child: TextFormField(
              controller: controller,
              textDirection: TextDirection.rtl,
              keyboardType: keyboardType,
              maxLines: maxLines,
              validator: validator,
              decoration: InputDecoration(
                labelText: label,
                prefixIcon: Icon(icon, color: AppTheme.primary, size: 22),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                  borderSide: const BorderSide(color: AppTheme.primary, width: 2),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
