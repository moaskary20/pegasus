import 'dart:io';
import 'package:file_picker/file_picker.dart';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import '../api/cart_api.dart';
import '../api/checkout_api.dart';
import '../api/config.dart';
import '../app_theme.dart';
import 'main_shell.dart';

/// بيانات السلة المرسلة من شاشة السلة كقيمة ابتدائية
class CartScreenCartData {
  CartScreenCartData({
    required this.courses,
    required this.products,
    required this.coursesSubtotal,
    required this.productsSubtotal,
    required this.total,
  });
  final List<CartCourseItem> courses;
  final List<CartProductItem> products;
  final double coursesSubtotal;
  final double productsSubtotal;
  final double total;
}

/// شاشة الدفع — معاينة الطلب، اختيار طريقة الدفع، إتمام الشراء
class CheckoutScreen extends StatefulWidget {
  const CheckoutScreen({super.key, this.initialCartData});

  final CartScreenCartData? initialCartData;

  @override
  State<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends State<CheckoutScreen> {
  bool _loading = true;
  bool _submitting = false;
  bool _validatingCoupon = false;
  CheckoutPreviewResponse? _preview;
  CouponValidationResult? _couponResult;
  String _selectedMethod = 'kashier';
  final _couponController = TextEditingController();
  File? _manualReceipt;

  /// تحويل بيانات السلة إلى صيغة المعاينة (للعرض عند فشل API)
  CheckoutPreviewResponse _cartDataToPreview(CartScreenCartData d) {
    final courses = d.courses
        .map((c) => CheckoutCourseItem(
              id: c.id,
              title: c.title,
              slug: c.slug,
              price: c.price,
              coverImage: c.coverImage,
            ))
        .toList();
    final products = d.products
        .map((p) => CheckoutProductItem(
              id: p.id,
              productId: p.productId,
              name: p.name,
              quantity: p.quantity,
              unitPrice: p.unitPrice,
              total: p.total,
            ))
        .toList();
    return CheckoutPreviewResponse(
      courses: courses,
      cartProducts: products,
      coursesSubtotal: d.coursesSubtotal,
      productsSubtotal: d.productsSubtotal,
      total: d.total,
      paymentMethods: CheckoutApi.defaultPaymentMethods,
      needsAuth: false,
    );
  }

  @override
  void initState() {
    super.initState();
    // عرض البيانات المرسلة فوراً إن وجدت
    if (widget.initialCartData != null && widget.initialCartData!.total > 0) {
      _preview = _cartDataToPreview(widget.initialCartData!);
      _loading = false;
    }
    _load();
  }

  @override
  void dispose() {
    _couponController.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    if (_preview == null) setState(() => _loading = true);
    final res = await CheckoutApi.getPreview();
    if (mounted) {
      final useApi = res.courses.isNotEmpty || res.cartProducts.isNotEmpty || !res.needsAuth;
      setState(() {
        if (useApi) {
          _preview = res;
        } else if (widget.initialCartData != null && widget.initialCartData!.total > 0) {
          // الإبقاء على البيانات المرسلة عند فشل أو فراغ API
          _preview = _cartDataToPreview(widget.initialCartData!);
        } else {
          _preview = res;
        }
        _loading = false;
        if (res.paymentMethods.isNotEmpty) {
          _selectedMethod = res.paymentMethods.first.id;
        }
      });
    }
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
          setState(() => _manualReceipt = File(result.files.first.path!));
        }
      } else {
        final picker = ImagePicker();
        final src = choice == 'gallery' ? ImageSource.gallery : ImageSource.camera;
        final file = await picker.pickImage(source: src, maxWidth: 1920, imageQuality: 85);
        if (file != null && mounted) {
          setState(() => _manualReceipt = File(file.path));
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
    if (_preview == null || _preview!.total <= 0) return;
    if (_selectedMethod == 'manual' && _manualReceipt == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('يرجى إرفاق إيصال التحويل')),
      );
      return;
    }

    setState(() => _submitting = true);
    final result = await CheckoutApi.process(
      paymentGateway: _selectedMethod,
      couponCode: _couponController.text.trim().isEmpty ? null : _couponController.text.trim(),
      manualReceiptFile: _selectedMethod == 'manual' ? _manualReceipt : null,
    );
    if (!mounted) return;
    setState(() => _submitting = false);

    if (result.success) {
      if (_selectedMethod == 'kashier') {
        // TODO: فتح بوابة كاشير أو إعادة التوجيه عند توفرها
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(result.message ?? 'تم إنشاء الطلب بنجاح')),
        );
      }
      Navigator.of(context).pushAndRemoveUntil(
        MaterialPageRoute(builder: (_) => const MainShell()),
        (_) => false,
      );
      return;
    }
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(result.message ?? 'حدث خطأ')),
    );
  }

  String _fullUrl(String? path) {
    if (path == null || path.isEmpty) return '';
    if (path.startsWith('http')) return path;
    final base = apiBaseUrl.endsWith('/') ? apiBaseUrl : '$apiBaseUrl/';
    return path.startsWith('/') ? '$base${path.substring(1)}' : '$base$path';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.surface,
      appBar: AppBar(
        backgroundColor: AppTheme.primary,
        foregroundColor: Colors.white,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded),
          onPressed: () => Navigator.maybePop(context),
        ),
        title: const Text('الدفع', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: AppTheme.primary))
          : _preview == null || _preview!.needsAuth
              ? _buildNeedsAuth()
              : _preview!.courses.isEmpty && _preview!.cartProducts.isEmpty
                  ? _buildEmptyState()
                  : RefreshIndicator(
                      onRefresh: _load,
                      color: AppTheme.primary,
                      child: SingleChildScrollView(
                        physics: const AlwaysScrollableScrollPhysics(parent: BouncingScrollPhysics()),
                        padding: const EdgeInsets.fromLTRB(20, 16, 20, 32),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
                            _buildItemsList(),
                            const SizedBox(height: 24),
                            _buildCouponField(),
                            const SizedBox(height: 24),
                            _buildPaymentMethods(),
                            if (_selectedMethod == 'manual') ...[
                              const SizedBox(height: 16),
                              _buildManualReceipt(),
                            ],
                            const SizedBox(height: 24),
                            _buildSummary(),
                          ],
                        ),
                      ),
                    ),
    );
  }

  Widget _buildItemsList() {
    if (_preview == null) return const SizedBox.shrink();
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.06),
            blurRadius: 16,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'محتويات الطلب',
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                  fontWeight: FontWeight.bold,
                  color: AppTheme.primaryDark,
                ),
          ),
          const SizedBox(height: 12),
          ...(_preview!.courses.map((c) => Padding(
                padding: const EdgeInsets.only(bottom: 8),
                child: Row(
                  textDirection: TextDirection.rtl,
                  children: [
                    ClipRRect(
                      borderRadius: BorderRadius.circular(12),
                      child: Image.network(
                        _fullUrl(c.coverImage),
                        width: 56,
                        height: 56,
                        fit: BoxFit.cover,
                        errorBuilder: (_, __, ___) => Container(
                          width: 56,
                          height: 56,
                          color: Colors.grey.shade200,
                          child: const Icon(Icons.school_rounded),
                        ),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            c.title,
                            style: const TextStyle(fontWeight: FontWeight.w600),
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                            textDirection: TextDirection.rtl,
                          ),
                          Text(
                            '${c.price.toStringAsFixed(1)} ر.س',
                            style: const TextStyle(color: AppTheme.primary, fontWeight: FontWeight.bold),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ))),
          ...(_preview!.cartProducts.map((p) => Padding(
                padding: const EdgeInsets.only(bottom: 8),
                child: Row(
                  textDirection: TextDirection.rtl,
                  children: [
                    Container(
                      width: 56,
                      height: 56,
                      decoration: BoxDecoration(
                        color: Colors.grey.shade200,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Icon(Icons.shopping_bag_rounded, size: 28),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            p.name,
                            style: const TextStyle(fontWeight: FontWeight.w600),
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                            textDirection: TextDirection.rtl,
                          ),
                          Text(
                            '${p.quantity} × ${p.unitPrice.toStringAsFixed(1)} = ${p.total.toStringAsFixed(1)} ر.س',
                            style: TextStyle(fontSize: 13, color: Colors.grey.shade600),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ))),
        ],
      ),
    );
  }

  Future<void> _applyCoupon() async {
    final code = _couponController.text.trim();
    if (code.isEmpty) {
      setState(() => _couponResult = null);
      return;
    }
    setState(() => _validatingCoupon = true);
    final res = await CheckoutApi.validateCoupon(code);
    if (mounted) setState(() {
      _couponResult = res;
      _validatingCoupon = false;
    });
    if (mounted && res.valid) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(res.message), behavior: SnackBarBehavior.floating),
      );
    } else if (mounted && !res.valid && res.message.isNotEmpty && res.message != 'أدخل رمز الكوبون') {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(res.message), behavior: SnackBarBehavior.floating, backgroundColor: Colors.red.shade700),
      );
    }
  }

  Widget _buildCouponField() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.06),
            blurRadius: 16,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'كود الخصم',
            style: Theme.of(context).textTheme.titleSmall?.copyWith(
                  fontWeight: FontWeight.bold,
                  color: AppTheme.primaryDark,
                ),
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: TextField(
                  controller: _couponController,
                  decoration: InputDecoration(
                    hintText: 'أدخل الكود',
                    hintTextDirection: TextDirection.rtl,
                    prefixIcon: const Icon(Icons.confirmation_number_outlined),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                    filled: true,
                  ),
                  textDirection: TextDirection.rtl,
                  onChanged: (_) => setState(() => _couponResult = null),
                ),
              ),
              const SizedBox(width: 12),
              FilledButton(
                onPressed: _validatingCoupon ? null : _applyCoupon,
                style: FilledButton.styleFrom(backgroundColor: AppTheme.primary),
                child: _validatingCoupon
                    ? const SizedBox(width: 24, height: 24, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                    : const Text('تطبيق'),
              ),
            ],
          ),
          if (_couponResult != null && _couponResult!.valid) ...[
            const SizedBox(height: 8),
            Text(
              '✓ تم تطبيق الكوبون ${_couponResult!.couponCode} — خصم ${_couponResult!.discount.toStringAsFixed(1)} ر.س',
              style: TextStyle(color: Colors.green.shade700, fontSize: 13),
              textDirection: TextDirection.rtl,
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildPaymentMethods() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.06),
            blurRadius: 16,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'طريقة الدفع',
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                  fontWeight: FontWeight.bold,
                  color: AppTheme.primaryDark,
                ),
          ),
          const SizedBox(height: 12),
          ...(_preview?.paymentMethods ?? []).map((m) => _PaymentMethodTile(
                method: m,
                selectedMethodId: _selectedMethod,
                onTap: () => setState(() => _selectedMethod = m.id),
              )),
        ],
      ),
    );
  }

  Widget _buildManualReceipt() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.amber.shade50,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.amber.shade200),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text(
            'إرفاق إيصال التحويل',
            style: Theme.of(context).textTheme.titleSmall?.copyWith(
                  fontWeight: FontWeight.bold,
                  color: AppTheme.primaryDark,
                ),
          ),
          const SizedBox(height: 8),
          if (_manualReceipt != null)
            Row(
              textDirection: TextDirection.rtl,
              children: [
                Icon(Icons.check_circle_rounded, color: Colors.green.shade700, size: 24),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    _manualReceipt!.path.split('/').last,
                    style: const TextStyle(fontSize: 13),
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
                TextButton(
                  onPressed: () => setState(() => _manualReceipt = null),
                  child: const Text('إزالة'),
                ),
              ],
            )
          else
            OutlinedButton.icon(
              onPressed: _pickReceipt,
              icon: const Icon(Icons.upload_file_rounded),
              label: const Text('اختر إيصال (JPG/PNG/PDF)'),
              style: OutlinedButton.styleFrom(
                padding: const EdgeInsets.symmetric(vertical: 12),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildSummary() {
    if (_preview == null) return const SizedBox.shrink();
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.06),
            blurRadius: 16,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          if (_preview!.courses.isNotEmpty)
            _SummaryRow(label: 'مجموع الدورات', value: '${_preview!.coursesSubtotal.toStringAsFixed(1)} ر.س'),
          if (_preview!.cartProducts.isNotEmpty)
            _SummaryRow(label: 'مجموع المنتجات', value: '${_preview!.productsSubtotal.toStringAsFixed(1)} ر.س'),
          if (_couponResult != null && _couponResult!.valid && _couponResult!.discount > 0)
            _SummaryRow(label: 'الخصم (${_couponResult!.couponCode})', value: '-${_couponResult!.discount.toStringAsFixed(1)} ر.س', valueStyle: TextStyle(color: Colors.green.shade700)),
          const Divider(height: 24),
          _SummaryRow(
            label: 'الإجمالي',
            value: '${(_couponResult != null && _couponResult!.valid ? _couponResult!.total : _preview!.total).toStringAsFixed(1)} ر.س',
            valueStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: AppTheme.primary),
          ),
          const SizedBox(height: 20),
          FilledButton(
            onPressed: _submitting
                ? null
                : () {
                    if (_selectedMethod == 'manual' && _manualReceipt == null) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('يرجى إرفاق إيصال التحويل')),
                      );
                      return;
                    }
                    _submit();
                  },
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
                : const Text('تأكيد الطلب', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  Widget _buildNeedsAuth() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.login_rounded, size: 64, color: AppTheme.primary.withValues(alpha: 0.6)),
            const SizedBox(height: 24),
            Text(
              'سجّل الدخول لإتمام الدفع',
              style: Theme.of(context).textTheme.titleLarge?.copyWith(
                    fontWeight: FontWeight.bold,
                    color: AppTheme.primaryDark,
                  ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.shopping_cart_outlined, size: 64, color: AppTheme.primary.withValues(alpha: 0.6)),
            const SizedBox(height: 24),
            Text(
              'السلة فارغة',
              style: Theme.of(context).textTheme.titleLarge?.copyWith(
                    fontWeight: FontWeight.bold,
                    color: AppTheme.primaryDark,
                  ),
            ),
            const SizedBox(height: 8),
            Text(
              'أضف عناصر إلى السلة أولاً',
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: Colors.grey.shade600),
            ),
          ],
        ),
      ),
    );
  }
}

class _PaymentMethodTile extends StatelessWidget {
  const _PaymentMethodTile({
    required this.method,
    required this.selectedMethodId,
    required this.onTap,
  });

  final PaymentMethod method;
  final String selectedMethodId;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final selected = selectedMethodId == method.id;
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
                  method.id == 'kashier' ? Icons.credit_card_rounded : Icons.receipt_long_rounded,
                  color: selected ? AppTheme.primary : Colors.grey.shade600,
                  size: 28,
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        method.label,
                        style: TextStyle(
                          fontWeight: FontWeight.bold,
                          color: selected ? AppTheme.primary : AppTheme.primaryDark,
                        ),
                        textDirection: TextDirection.rtl,
                      ),
                      if (method.description != null && method.description!.isNotEmpty)
                        Text(
                          method.description!,
                          style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                          textDirection: TextDirection.rtl,
                        ),
                    ],
                  ),
                ),
                Radio<String>(
                  value: method.id,
                  groupValue: selectedMethodId,
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

class _SummaryRow extends StatelessWidget {
  const _SummaryRow({required this.label, required this.value, this.valueStyle});

  final String label;
  final String value;
  final TextStyle? valueStyle;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        textDirection: TextDirection.rtl,
        children: [
          Text(label, style: TextStyle(fontSize: 14, color: Colors.grey.shade700)),
          Text(value, style: valueStyle ?? const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
        ],
      ),
    );
  }
}
