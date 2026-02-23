import 'package:flutter/material.dart';
import '../widgets/app_header.dart';

const Color _primary = Color(0xFF2c004d);

/// تبويب البحث
class SearchTab extends StatelessWidget {
  const SearchTab({super.key, this.onOpenDrawer});

  final VoidCallback? onOpenDrawer;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppHeader(
        title: 'البحث',
        onMenu: onOpenDrawer ?? () => Scaffold.of(context).openDrawer(),
      ),
      body: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          children: [
            TextField(
              textDirection: TextDirection.rtl,
              decoration: InputDecoration(
                hintText: 'ابحث عن دورة أو مدرب...',
                prefixIcon: const Icon(Icons.search_rounded, color: _primary),
                filled: true,
                fillColor: Colors.grey.shade100,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(16),
                  borderSide: BorderSide.none,
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(16),
                  borderSide: const BorderSide(color: _primary, width: 2),
                ),
              ),
            ),
            const SizedBox(height: 32),
            Icon(Icons.search_rounded, size: 80, color: _primary.withOpacity(0.3)),
            const SizedBox(height: 16),
            Text(
              'ابحث عن الدورات والمواد',
              style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    color: Colors.grey.shade600,
                  ),
            ),
          ],
        ),
      ),
    );
  }
}
