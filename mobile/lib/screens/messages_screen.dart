import 'package:flutter/material.dart';
import '../app_theme.dart';
import '../api/messages_api.dart';
import 'feature_scaffold.dart';

/// الرسائل — مربوط بـ api/messages/recent مع حركات
class MessagesScreen extends StatefulWidget {
  const MessagesScreen({super.key});

  @override
  State<MessagesScreen> createState() => _MessagesScreenState();
}

class _MessagesScreenState extends State<MessagesScreen> {
  bool _loading = true;
  List<ConversationPreview> _list = [];
  int _unreadCount = 0;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    final res = await MessagesApi.getRecent(limit: 30);
    if (!mounted) return;
    setState(() {
      _list = res.conversations;
      _unreadCount = res.unreadCount;
      _loading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    return FeatureScaffold(
      title: 'الرسائل',
      body: RefreshIndicator(
        onRefresh: _load,
        color: AppTheme.primary,
        child: _loading
            ? const Padding(
                padding: EdgeInsets.all(48),
                child: Center(child: CircularProgressIndicator(color: AppTheme.primary)),
              )
            : _list.isEmpty
                ? _EmptyMessages(onRefresh: _load)
                : Padding(
                    padding: const EdgeInsets.all(20),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        if (_unreadCount > 0)
                          Padding(
                            padding: const EdgeInsets.only(bottom: 16),
                            child: TweenAnimationBuilder<double>(
                              tween: AppTheme.fadeInTween(),
                              duration: AppTheme.animFast,
                              builder: (context, value, _) => Opacity(
                                opacity: value,
                                child: Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                                  decoration: BoxDecoration(
                                    color: AppTheme.primary.withValues(alpha: 0.1),
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  child: Text(
                                    '$_unreadCount محادثات غير مقروءة',
                                    style: Theme.of(context).textTheme.labelLarge?.copyWith(
                                          color: AppTheme.primary,
                                          fontWeight: FontWeight.bold,
                                        ),
                                  ),
                                ),
                              ),
                            ),
                          ),
                        ListView.builder(
                          shrinkWrap: true,
                          physics: const NeverScrollableScrollPhysics(),
                          itemCount: _list.length,
                          itemBuilder: (context, index) {
                            final c = _list[index];
                            return _ConversationTile(index: index, conversation: c);
                          },
                        ),
                      ],
                    ),
                  ),
      ),
    );
  }
}

class _ConversationTile extends StatelessWidget {
  const _ConversationTile({required this.index, required this.conversation});

  final int index;
  final ConversationPreview conversation;

  @override
  Widget build(BuildContext context) {
    final hasUnread = conversation.unread > 0;
    return TweenAnimationBuilder<double>(
      tween: AppTheme.fadeInTween(),
      duration: Duration(milliseconds: 260 + (index * 40)),
      curve: AppTheme.curveDefault,
      builder: (context, value, _) => Opacity(
        opacity: value,
        child: Transform.translate(
          offset: Offset(0, 16 * (1 - value)),
          child: Padding(
            padding: const EdgeInsets.only(bottom: 12),
            child: Material(
              color: hasUnread ? AppTheme.primary.withValues(alpha: 0.06) : Colors.white,
              borderRadius: BorderRadius.circular(16),
              elevation: 0,
              child: InkWell(
                onTap: () {
                  // يمكن لاحقاً فتح شاشة المحادثة عند توفر API للرسائل الفردية
                },
                borderRadius: BorderRadius.circular(16),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Row(
                    children: [
                      CircleAvatar(
                        radius: 28,
                        backgroundColor: AppTheme.primary.withValues(alpha: 0.2),
                        child: Text(
                          conversation.name.isNotEmpty ? conversation.name.substring(0, 1).toUpperCase() : '؟',
                          style: const TextStyle(
                            color: AppTheme.primary,
                            fontWeight: FontWeight.bold,
                            fontSize: 20,
                          ),
                        ),
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                Expanded(
                                  child: Text(
                                    conversation.name,
                                    style: Theme.of(context).textTheme.titleSmall?.copyWith(
                                          fontWeight: hasUnread ? FontWeight.bold : FontWeight.w600,
                                          color: AppTheme.primaryDark,
                                        ),
                                  ),
                                ),
                                if (conversation.lastAt != null)
                                  Text(
                                    conversation.lastAt!,
                                    style: Theme.of(context).textTheme.bodySmall?.copyWith(
                                          color: Colors.grey.shade500,
                                        ),
                                  ),
                              ],
                            ),
                            const SizedBox(height: 4),
                            Text(
                              conversation.lastPreview,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                                    color: Colors.grey.shade600,
                                  ),
                            ),
                            if (hasUnread && conversation.unread > 0) ...[
                              const SizedBox(height: 6),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                decoration: BoxDecoration(
                                  color: AppTheme.primary,
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: Text(
                                  '${conversation.unread}',
                                  style: const TextStyle(
                                    color: Colors.white,
                                    fontSize: 12,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ),
                            ],
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class _EmptyMessages extends StatelessWidget {
  const _EmptyMessages({required this.onRefresh});

  final VoidCallback onRefresh;

  @override
  Widget build(BuildContext context) {
    return TweenAnimationBuilder<double>(
      tween: AppTheme.scaleInTween(),
      duration: AppTheme.animSlow,
      curve: AppTheme.curveEmphasized,
      builder: (context, value, _) => Opacity(
        opacity: value,
        child: Transform.scale(
          scale: value,
          child: Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(Icons.chat_bubble_outline_rounded, size: 72, color: Colors.grey.shade400),
                const SizedBox(height: 16),
                Text(
                  'لا توجد محادثات بعد',
                  style: Theme.of(context).textTheme.titleLarge?.copyWith(
                        fontWeight: FontWeight.bold,
                        color: AppTheme.primaryDark,
                      ),
                ),
                const SizedBox(height: 8),
                Text(
                  'عند بدء محادثة مع مدرب أو الدعم ستظهر هنا',
                  textAlign: TextAlign.center,
                  style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: Colors.grey.shade600),
                ),
                const SizedBox(height: 16),
                TextButton.icon(
                  onPressed: onRefresh,
                  icon: const Icon(Icons.refresh_rounded, size: 20),
                  label: const Text('تحديث'),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
