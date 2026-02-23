import 'package:flutter/material.dart';
import '../widgets/app_header.dart';

const Color _primary = Color(0xFF2c004d);

/// تبويب دوراتى
class MyCoursesTab extends StatelessWidget {
  const MyCoursesTab({super.key, this.onOpenDrawer});

  final VoidCallback? onOpenDrawer;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppHeader(
        title: 'دوراتى',
        onMenu: onOpenDrawer ?? () => Scaffold.of(context).openDrawer(),
      ),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.menu_book_rounded, size: 80, color: _primary.withOpacity(0.3)),
            const SizedBox(height: 16),
            Text(
              'الدورات المسجلة فيها ستظهر هنا',
              style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    color: Colors.grey.shade600,
                  ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }
}
