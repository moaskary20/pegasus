import 'dart:io';
import 'package:flutter/material.dart';
import 'package:file_picker/file_picker.dart';
import '../app_theme.dart';
import '../api/my_assignments_api.dart';
import 'feature_scaffold.dart';

/// شاشة تسليم واجب
class AssignmentSubmitScreen extends StatefulWidget {
  const AssignmentSubmitScreen({super.key, required this.assignmentId});

  final int assignmentId;

  @override
  State<AssignmentSubmitScreen> createState() => _AssignmentSubmitScreenState();
}

class _AssignmentSubmitScreenState extends State<AssignmentSubmitScreen> {
  AssignmentDetailResponse? _detail;
  bool _loading = true;
  String? _error;
  final _contentController = TextEditingController();
  final List<File> _selectedFiles = [];
  bool _sending = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _contentController.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    final res = await MyAssignmentsApi.getAssignmentDetail(widget.assignmentId);
    if (!mounted) return;
    setState(() {
      _detail = res;
      _error = res == null ? 'تعذر تحميل تفاصيل الواجب' : null;
      _loading = false;
    });
  }

  Future<void> _pickFiles() async {
    final result = await FilePicker.platform.pickFiles(
      allowMultiple: true,
      type: FileType.custom,
      allowedExtensions: _detail?.allowedFileTypes.isNotEmpty == true
          ? (_detail!.allowedFileTypes.map((e) => e.toString()).toList())
          : ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'zip', 'rar'],
    );
    if (result == null || !mounted) return;
    final maxMb = _detail?.maxFileSizeMb ?? 10;
    final maxBytes = (maxMb * 1024 * 1024).toInt();
    for (final f in result.files) {
      if (f.path != null) {
        final file = File(f.path!);
        if (await file.exists()) {
          final size = await file.length();
          if (size <= maxBytes) {
            setState(() => _selectedFiles.add(file));
          } else {
            if (mounted) {
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(content: Text('الملف ${f.name} يتجاوز الحد المسموح ($maxMb م.ب)')),
              );
            }
          }
        }
      }
    }
  }

  void _removeFile(int index) {
    setState(() => _selectedFiles.removeAt(index));
  }

  Future<void> _submit() async {
    if (_detail == null || !_detail!.canSubmit) return;
    final content = _contentController.text.trim();
    if (content.isEmpty && _selectedFiles.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('أضف محتوى نصياً أو ملفات للتسليم')),
      );
      return;
    }
    setState(() => _sending = true);
    final result = await MyAssignmentsApi.submitAssignment(
      widget.assignmentId,
      content: content,
      files: _selectedFiles,
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
    if (result.success) {
      Navigator.of(context).pop(true);
    }
  }

  @override
  Widget build(BuildContext context) {
    return FeatureScaffold(
      title: 'تسليم الواجب',
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: AppTheme.primary))
          : _error != null
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.error_outline_rounded, size: 64, color: Colors.grey.shade600),
                      const SizedBox(height: 16),
                      Text(_error!, textAlign: TextAlign.center),
                      const SizedBox(height: 16),
                      TextButton.icon(
                        onPressed: _load,
                        icon: const Icon(Icons.refresh_rounded),
                        label: const Text('إعادة المحاولة'),
                      ),
                    ],
                  ),
                )
              : _detail == null
                  ? const SizedBox.shrink()
                  : SingleChildScrollView(
                      padding: const EdgeInsets.all(20),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          _buildHeader(),
                          const SizedBox(height: 24),
                          if (!_detail!.canSubmit)
                            Container(
                              padding: const EdgeInsets.all(16),
                              decoration: BoxDecoration(
                                color: Colors.orange.withValues(alpha: 0.1),
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: Row(
                                children: [
                                  Icon(Icons.info_outline_rounded, color: Colors.orange.shade700),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: Text(
                                      'لا يمكنك تسليم هذا الواجب حالياً. تأكد من مواعيد التسليم أو اكتمال التسجيل.',
                                      style: TextStyle(color: Colors.orange.shade900),
                                    ),
                                  ),
                                ],
                              ),
                            )
                          else ...[
                            Text(
                              'المحتوى النصي (اختياري)',
                              style: Theme.of(context).textTheme.titleSmall?.copyWith(
                                    fontWeight: FontWeight.w600,
                                    color: AppTheme.primaryDark,
                                  ),
                            ),
                            const SizedBox(height: 8),
                            TextField(
                              controller: _contentController,
                              maxLines: 5,
                              textDirection: TextDirection.rtl,
                              decoration: InputDecoration(
                                hintText: 'اكتب إجابتك أو وصف التسليم...',
                                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                                focusedBorder: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                  borderSide: const BorderSide(color: AppTheme.primary, width: 2),
                                ),
                              ),
                            ),
                            const SizedBox(height: 20),
                            Text(
                              'الملفات',
                              style: Theme.of(context).textTheme.titleSmall?.copyWith(
                                    fontWeight: FontWeight.w600,
                                    color: AppTheme.primaryDark,
                                  ),
                            ),
                            const SizedBox(height: 8),
                            OutlinedButton.icon(
                              onPressed: _pickFiles,
                              icon: const Icon(Icons.attach_file_rounded, size: 20),
                              label: const Text('إضافة ملفات'),
                              style: OutlinedButton.styleFrom(
                                foregroundColor: AppTheme.primary,
                                side: const BorderSide(color: AppTheme.primary),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                              ),
                            ),
                            if (_selectedFiles.isNotEmpty) ...[
                              const SizedBox(height: 12),
                              ...List.generate(_selectedFiles.length, (i) {
                                final f = _selectedFiles[i];
                                final name = f.path.split('/').last;
                                return Padding(
                                  padding: const EdgeInsets.only(bottom: 8),
                                  child: Row(
                                    children: [
                                      Icon(Icons.insert_drive_file_rounded, color: AppTheme.primary, size: 22),
                                      const SizedBox(width: 8),
                                      Expanded(child: Text(name, overflow: TextOverflow.ellipsis)),
                                      IconButton(
                                        icon: const Icon(Icons.close_rounded, size: 20),
                                        onPressed: () => _removeFile(i),
                                        style: IconButton.styleFrom(
                                          foregroundColor: Colors.red,
                                          minimumSize: const Size(36, 36),
                                        ),
                                      ),
                                    ],
                                  ),
                                );
                              }),
                            ],
                            const SizedBox(height: 24),
                            SizedBox(
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
                                    : const Text('تسليم الواجب'),
                              ),
                            ),
                          ],
                        ],
                      ),
                    ),
    );
  }

  Widget _buildHeader() {
    final d = _detail!;
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppTheme.primary.withValues(alpha: 0.06),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            d.title,
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                  fontWeight: FontWeight.bold,
                  color: AppTheme.primaryDark,
                ),
          ),
          const SizedBox(height: 4),
          Text(d.courseTitle, style: Theme.of(context).textTheme.bodySmall?.copyWith(color: Colors.grey.shade600)),
          if (d.dueDate != null) ...[
            const SizedBox(height: 4),
            Text(
              'آخر موعد: ${d.dueDate}',
              style: TextStyle(
                fontSize: 12,
                color: d.isOverdue ? Colors.red.shade700 : Colors.grey.shade600,
              ),
            ),
          ],
          if (d.instructions.isNotEmpty) ...[
            const SizedBox(height: 12),
            Text(
              d.instructions,
              style: Theme.of(context).textTheme.bodySmall?.copyWith(color: Colors.grey.shade700),
            ),
          ],
        ],
      ),
    );
  }
}
