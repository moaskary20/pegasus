import 'dart:io';
import 'package:file_picker/file_picker.dart';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import '../api/auth_api.dart';
import '../api/subscriptions_api.dart';
import '../app_theme.dart';
import 'feature_scaffold.dart';

/// الاشتراكات — الخطط المتاحة + اشتراكاتي + إمكانية الاشتراك
class SubscriptionsScreen extends StatefulWidget {
  const SubscriptionsScreen({super.key});

  @override
  State<SubscriptionsScreen> createState() => _SubscriptionsScreenState();
}

class _SubscriptionsScreenState extends State<SubscriptionsScreen> {
  bool _loading = true;
  List<SubscriptionPlanItem> _plans = [];
  List<MySubscriptionItem> _mySubscriptions = [];
  bool _needsAuth = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    await AuthApi.loadStoredToken();
    final plansRes = await SubscriptionsApi.getPlans();
    final myRes = await SubscriptionsApi.getMySubscriptions();
    if (mounted) {
      setState(() {
        _plans = plansRes.plans;
        _mySubscriptions = myRes.subscriptions;
        _needsAuth = myRes.needsAuth;
        _loading = false;
      });
    }
  }

  Future<void> _openSubscribeSheet(SubscriptionPlanItem plan) async {
    final result = await showModalBottomSheet<SubscribeResult?>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _SubscribeSheet(
        plan: plan,
        onSubscribe: SubscriptionsApi.subscribe,
      ),
    );
    if (result != null && result.success && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(result.message ?? 'تم الاشتراك بنجاح')),
      );
      _load();
    }
  }

  @override
  Widget build(BuildContext context) {
    return FeatureScaffold(
      title: 'الاشتراكات',
      body: RefreshIndicator(
        onRefresh: _load,
        color: AppTheme.primary,
        child: _loading
            ? const Center(child: CircularProgressIndicator(color: AppTheme.primary))
            : CustomScrollView(
                    physics: const AlwaysScrollableScrollPhysics(parent: BouncingScrollPhysics()),
                    slivers: [
                      if (_needsAuth)
                        SliverToBoxAdapter(
                          child: Container(
                            margin: const EdgeInsets.fromLTRB(20, 16, 20, 8),
                            padding: const EdgeInsets.all(16),
                            decoration: BoxDecoration(
                              color: AppTheme.primary.withValues(alpha: 0.08),
                              borderRadius: BorderRadius.circular(16),
                            ),
                            child: Row(
                              textDirection: TextDirection.rtl,
                              children: [
                                Icon(Icons.info_outline_rounded, color: AppTheme.primary),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Text(
                                    'سجّل الدخول لعرض اشتراكاتك والاشتراك في الخطط',
                                    style: TextStyle(color: AppTheme.primaryDark, fontWeight: FontWeight.w500),
                                    textDirection: TextDirection.rtl,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      SliverToBoxAdapter(
                        child: Padding(
                          padding: const EdgeInsets.fromLTRB(20, 16, 20, 8),
                          child: Text(
                            'الخطط المتاحة',
                            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                                  fontWeight: FontWeight.bold,
                                  color: AppTheme.primaryDark,
                                ),
                          ),
                        ),
                      ),
                      if (_plans.isEmpty)
                        SliverFillRemaining(
                          hasScrollBody: false,
                          child: _EmptyPlans(),
                        )
                      else
                        SliverPadding(
                          padding: const EdgeInsets.fromLTRB(20, 0, 20, 24),
                          sliver: SliverList(
                            delegate: SliverChildBuilderDelegate(
                              (_, i) => _PlanCard(
                                plan: _plans[i],
                                onSubscribe: () => _openSubscribeSheet(_plans[i]),
                              ),
                              childCount: _plans.length,
                            ),
                          ),
                        ),
                      if (_mySubscriptions.isNotEmpty) ...[
                        SliverToBoxAdapter(
                          child: Padding(
                            padding: const EdgeInsets.fromLTRB(20, 16, 20, 8),
                            child: Text(
                              'اشتراكاتي',
                              style: Theme.of(context).textTheme.titleMedium?.copyWith(
                                    fontWeight: FontWeight.bold,
                                    color: AppTheme.primaryDark,
                                  ),
                            ),
                          ),
                        ),
                        SliverPadding(
                          padding: const EdgeInsets.fromLTRB(20, 0, 20, 32),
                          sliver: SliverList(
                            delegate: SliverChildBuilderDelegate(
                              (_, i) => _MySubscriptionTile(item: _mySubscriptions[i]),
                              childCount: _mySubscriptions.length,
                            ),
                          ),
                        ),
                      ],
                    ],
                  ),
      ),
    );
  }
}

class _PlanCard extends StatelessWidget {
  const _PlanCard({
    required this.plan,
    required this.onSubscribe,
  });

  final SubscriptionPlanItem plan;
  final VoidCallback onSubscribe;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 16),
      child: Material(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        elevation: 0,
        shadowColor: Colors.black26,
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Row(
                textDirection: TextDirection.rtl,
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          plan.name,
                          style: Theme.of(context).textTheme.titleMedium?.copyWith(
                                fontWeight: FontWeight.bold,
                                color: AppTheme.primaryDark,
                              ),
                          textDirection: TextDirection.rtl,
                        ),
                        const SizedBox(height: 4),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: AppTheme.primary.withValues(alpha: 0.12),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text(
                            plan.typeLabelAr,
                            style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppTheme.primary),
                            textDirection: TextDirection.rtl,
                          ),
                        ),
                      ],
                    ),
                  ),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Text(
                        '${plan.price.toStringAsFixed(1)} ر.س',
                        style: const TextStyle(
                          fontSize: 22,
                          fontWeight: FontWeight.bold,
                          color: AppTheme.primary,
                        ),
                      ),
                      Text(
                        '${plan.durationDays} يوم',
                        style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                        textDirection: TextDirection.rtl,
                      ),
                    ],
                  ),
                ],
              ),
              if (plan.description != null && plan.description!.isNotEmpty) ...[
                const SizedBox(height: 12),
                Text(
                  plan.description!,
                  style: TextStyle(fontSize: 14, color: Colors.grey.shade700),
                  maxLines: 3,
                  overflow: TextOverflow.ellipsis,
                  textDirection: TextDirection.rtl,
                ),
              ],
              const SizedBox(height: 16),
              FilledButton(
                onPressed: onSubscribe,
                style: FilledButton.styleFrom(
                  backgroundColor: AppTheme.primary,
                  padding: const EdgeInsets.symmetric(vertical: 12),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                ),
                child: const Text('اشتراك الآن', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _MySubscriptionTile extends StatelessWidget {
  const _MySubscriptionTile({required this.item});

  final MySubscriptionItem item;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Material(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        elevation: 0,
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            textDirection: TextDirection.rtl,
            children: [
              Container(
                width: 48,
                height: 48,
                decoration: BoxDecoration(
                  color: item.isActive
                      ? AppTheme.primary.withValues(alpha: 0.12)
                      : Colors.grey.shade200,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(
                  Icons.card_membership_rounded,
                  color: item.isActive ? AppTheme.primary : Colors.grey.shade600,
                  size: 24,
                ),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      item.planName,
                      style: Theme.of(context).textTheme.titleSmall?.copyWith(
                            fontWeight: FontWeight.bold,
                            color: AppTheme.primaryDark,
                          ),
                      textDirection: TextDirection.rtl,
                    ),
                    const SizedBox(height: 4),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(
                        color: item.isActive
                            ? Colors.green.withValues(alpha: 0.15)
                            : Colors.grey.shade200,
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        item.isActive ? 'فعال' : 'منتهي',
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                          color: item.isActive ? Colors.green.shade700 : Colors.grey.shade700,
                        ),
                        textDirection: TextDirection.rtl,
                      ),
                    ),
                    if (item.endDate != null) ...[
                      const SizedBox(height: 4),
                      Text(
                        'ينتهي: ${item.endDate}',
                        style: Theme.of(context).textTheme.bodySmall?.copyWith(color: Colors.grey.shade600),
                        textDirection: TextDirection.rtl,
                      ),
                    ],
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _EmptyPlans extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.card_membership_outlined, size: 64, color: AppTheme.primary.withValues(alpha: 0.6)),
            const SizedBox(height: 16),
            Text(
              'لا توجد خطط اشتراك متاحة حالياً',
              textAlign: TextAlign.center,
              style: Theme.of(context).textTheme.titleMedium?.copyWith(color: AppTheme.primaryDark),
            ),
          ],
        ),
      ),
    );
  }
}

class _SubscribeSheet extends StatefulWidget {
  const _SubscribeSheet({
    required this.plan,
    required this.onSubscribe,
  });

  final SubscriptionPlanItem plan;
  final Future<SubscribeResult> Function({
    required int planId,
    required String paymentGateway,
    String? voucherCode,
    File? manualReceiptFile,
  }) onSubscribe;

  @override
  State<_SubscribeSheet> createState() => _SubscribeSheetState();
}

class _SubscribeSheetState extends State<_SubscribeSheet> {
  String _method = 'kashier';
  final _voucherController = TextEditingController();
  File? _receipt;
  bool _submitting = false;

  @override
  void dispose() {
    _voucherController.dispose();
    super.dispose();
  }

  Future<void> _pickReceipt() async {
    final choice = await showModalBottomSheet<String>(
      context: context,
      builder: (_) => SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            ListTile(
              leading: const Icon(Icons.photo_library_rounded),
              title: const Text('من المعرض'),
              onTap: () => Navigator.pop(context, 'gallery'),
            ),
            ListTile(
              leading: const Icon(Icons.camera_alt_rounded),
              title: const Text('التقاط صورة'),
              onTap: () => Navigator.pop(context, 'camera'),
            ),
            ListTile(
              leading: const Icon(Icons.picture_as_pdf_rounded),
              title: const Text('ملف PDF'),
              onTap: () => Navigator.pop(context, 'file'),
            ),
          ],
        ),
      ),
    );
    if (choice == null || !mounted) return;
    try {
      if (choice == 'file') {
        final result = await FilePicker.platform.pickFiles(
          type: FileType.custom,
          allowedExtensions: ['jpg', 'jpeg', 'png', 'pdf'],
        );
        if (result != null &&
            result.files.isNotEmpty &&
            result.files.first.path != null &&
            mounted) {
          setState(() => _receipt = File(result.files.first.path!));
        }
      } else {
        final picker = ImagePicker();
        final src = choice == 'gallery' ? ImageSource.gallery : ImageSource.camera;
        final file = await picker.pickImage(source: src, maxWidth: 1920, imageQuality: 85);
        if (file != null && mounted) {
          setState(() => _receipt = File(file.path));
        }
      }
    } catch (_) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('تعذر اختيار الملف')),
        );
      }
    }
  }

  Future<void> _submit() async {
    if (_method == 'manual' && _receipt == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('يرجى إرفاق إيصال التحويل')),
      );
      return;
    }

    setState(() => _submitting = true);
    final result = await widget.onSubscribe(
      planId: widget.plan.id,
      paymentGateway: _method,
      voucherCode: _voucherController.text.trim().isEmpty ? null : _voucherController.text.trim(),
      manualReceiptFile: _method == 'manual' ? _receipt : null,
    );
    if (!mounted) return;
    setState(() => _submitting = false);
    Navigator.of(context).pop(result);
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
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
            const SizedBox(height: 16),
            Text(
              'الاشتراك في ${widget.plan.name}',
              style: Theme.of(context).textTheme.titleLarge?.copyWith(
                    fontWeight: FontWeight.bold,
                    color: AppTheme.primaryDark,
                  ),
              textDirection: TextDirection.rtl,
            ),
            Text(
              '${widget.plan.price.toStringAsFixed(1)} ر.س — ${widget.plan.typeLabelAr}',
              style: TextStyle(color: AppTheme.primary, fontWeight: FontWeight.w600),
              textDirection: TextDirection.rtl,
            ),
            const SizedBox(height: 20),
            TextField(
              controller: _voucherController,
              decoration: InputDecoration(
                labelText: 'كود الخصم (اختياري)',
                prefixIcon: const Icon(Icons.confirmation_number_outlined),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                filled: true,
              ),
              onChanged: (_) => setState(() {}),
            ),
            const SizedBox(height: 16),
            Text(
              'طريقة الدفع',
              style: Theme.of(context).textTheme.titleSmall?.copyWith(
                    fontWeight: FontWeight.bold,
                    color: AppTheme.primaryDark,
                  ),
              textDirection: TextDirection.rtl,
            ),
            const SizedBox(height: 8),
            _PaymentOption(
              label: 'الدفع بالفيزا والبطاقات',
              value: 'kashier',
              groupValue: _method,
              onTap: () => setState(() => _method = 'kashier'),
            ),
            _PaymentOption(
              label: 'تحويل/دفع يدوي',
              value: 'manual',
              groupValue: _method,
              onTap: () => setState(() => _method = 'manual'),
            ),
            if (_method == 'manual') ...[
              const SizedBox(height: 12),
              if (_receipt != null)
                Row(
                  textDirection: TextDirection.rtl,
                  children: [
                    Icon(Icons.check_circle_rounded, color: Colors.green.shade700, size: 24),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        _receipt!.path.split('/').last,
                        style: const TextStyle(fontSize: 13),
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    TextButton(
                      onPressed: () => setState(() => _receipt = null),
                      child: const Text('إزالة'),
                    ),
                  ],
                )
              else
                OutlinedButton.icon(
                  onPressed: _pickReceipt,
                  icon: const Icon(Icons.upload_file_rounded),
                  label: const Text('إرفاق إيصال التحويل'),
                  style: OutlinedButton.styleFrom(
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                ),
            ],
            const SizedBox(height: 24),
            FilledButton(
              onPressed: _submitting ? null : _submit,
              style: FilledButton.styleFrom(
                backgroundColor: AppTheme.primary,
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
              ),
              child: _submitting
                  ? const SizedBox(
                      height: 24,
                      width: 24,
                      child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                    )
                  : const Text('تأكيد الاشتراك', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
            ),
          ],
        ),
      ),
    );
  }
}

class _PaymentOption extends StatelessWidget {
  const _PaymentOption({
    required this.label,
    required this.value,
    required this.groupValue,
    required this.onTap,
  });

  final String label;
  final String value;
  final String groupValue;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final selected = groupValue == value;
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Material(
        color: selected ? AppTheme.primary.withValues(alpha: 0.08) : Colors.grey.shade50,
        borderRadius: BorderRadius.circular(14),
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(14),
          child: Padding(
            padding: const EdgeInsets.all(14),
            child: Row(
              textDirection: TextDirection.rtl,
              children: [
                Icon(
                  value == 'kashier' ? Icons.credit_card_rounded : Icons.receipt_long_rounded,
                  color: selected ? AppTheme.primary : Colors.grey.shade600,
                  size: 24,
                ),
                const SizedBox(width: 12),
                Text(
                  label,
                  style: TextStyle(
                    fontWeight: FontWeight.w600,
                    color: selected ? AppTheme.primary : AppTheme.primaryDark,
                  ),
                  textDirection: TextDirection.rtl,
                ),
                const Spacer(),
                Radio<String>(
                  value: value,
                  groupValue: groupValue,
                  onChanged: (_) => onTap(),
                  activeColor: AppTheme.primary,
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
