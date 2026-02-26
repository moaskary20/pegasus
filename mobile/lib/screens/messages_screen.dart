import 'package:flutter/material.dart';
import '../app_theme.dart';
import '../api/auth_api.dart';
import '../api/messages_api.dart';
import 'conversation_screen.dart';
import 'feature_scaffold.dart';
import 'login_screen.dart';

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
  bool _needsAuth = false;
  bool _networkError = false;

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
      _needsAuth = res.needsAuth;
      _networkError = res.networkError;
      _loading = false;
    });
  }

  void _showNewConversation() {
    if (AuthApi.token == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('يجب تسجيل الدخول للدردشة'), behavior: SnackBarBehavior.floating),
      );
      return;
    }
    showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => _NewConversationSheet(
        onConversationStarted: (convId, name) {
          Navigator.pop(ctx);
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (_) => ConversationScreen(conversationId: convId, conversationName: name),
            ),
          ).then((_) => _load());
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final minHeight = MediaQuery.sizeOf(context).height -
        (kToolbarHeight + MediaQuery.paddingOf(context).top + 8);
    return FeatureScaffold(
      title: 'الرسائل',
      floatingActionButton: AuthApi.token != null
          ? FloatingActionButton(
              onPressed: _showNewConversation,
              backgroundColor: AppTheme.primary,
              child: const Icon(Icons.add_rounded, color: Colors.white),
            )
          : null,
      body: SizedBox(
        height: minHeight,
        child: RefreshIndicator(
          onRefresh: _load,
          color: AppTheme.primary,
          child: CustomScrollView(
            physics: const AlwaysScrollableScrollPhysics(parent: BouncingScrollPhysics()),
            slivers: [
              if (_loading)
                const SliverFillRemaining(
                  hasScrollBody: false,
                  child: Center(child: CircularProgressIndicator(color: AppTheme.primary)),
                )
              else if (_needsAuth)
                SliverFillRemaining(
                  hasScrollBody: false,
                  child: AuthApi.token != null
                      ? _SessionExpiredMessages(onRefresh: _load)
                      : _NeedsAuthMessages(onRefresh: _load),
                )
              else if (_networkError)
                SliverFillRemaining(
                  hasScrollBody: false,
                  child: _NetworkErrorMessages(onRefresh: _load),
                )
              else if (_list.isEmpty)
                SliverFillRemaining(
                  hasScrollBody: false,
                  child: _EmptyMessages(onRefresh: _load),
                )
              else
                SliverPadding(
                  padding: const EdgeInsets.all(20),
                  sliver: SliverList(
                    delegate: SliverChildBuilderDelegate(
                      (context, index) {
                        if (index == 0 && _unreadCount > 0) {
                          return Padding(
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
                          );
                        }
                        final i = _unreadCount > 0 ? index - 1 : index;
                        if (i < 0) return const SizedBox.shrink();
                        final c = _list[i];
                        return Padding(
                          padding: const EdgeInsets.only(bottom: 12),
                          child: _ConversationTile(
                            index: i,
                            conversation: c,
                            onTap: () => Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (_) => ConversationScreen(
                                  conversationId: c.id,
                                  conversationName: c.name,
                                ),
                              ),
                            ).then((_) => _load()),
                          ),
                        );
                      },
                      childCount: _list.length + (_unreadCount > 0 ? 1 : 0),
                    ),
                  ),
                ),
            ],
          ),
        ),
      ),
    );
  }
}

class _ConversationTile extends StatelessWidget {
  const _ConversationTile({
    required this.index,
    required this.conversation,
    required this.onTap,
  });

  final int index;
  final ConversationPreview conversation;
  final VoidCallback onTap;

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
                onTap: onTap,
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

class _NetworkErrorMessages extends StatelessWidget {
  const _NetworkErrorMessages({required this.onRefresh});

  final VoidCallback onRefresh;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.wifi_off_rounded, size: 72, color: AppTheme.primary.withValues(alpha: 0.6)),
          const SizedBox(height: 20),
          Text(
            'تحقق من الاتصال بالإنترنت',
            style: Theme.of(context).textTheme.titleLarge?.copyWith(
                  fontWeight: FontWeight.bold,
                  color: AppTheme.primaryDark,
                ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 8),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 32),
            child: Text(
              'تعذر الاتصال بالخادم. تأكد من اتصالك بالإنترنت وجرب التحديث',
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: Colors.grey.shade600),
              textAlign: TextAlign.center,
            ),
          ),
          const SizedBox(height: 16),
          FilledButton.icon(
            onPressed: onRefresh,
            icon: const Icon(Icons.refresh_rounded, size: 20),
            label: const Text('تحديث'),
            style: FilledButton.styleFrom(
              backgroundColor: AppTheme.primary,
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
            ),
          ),
        ],
      ),
    );
  }
}

class _NeedsAuthMessages extends StatelessWidget {
  const _NeedsAuthMessages({required this.onRefresh});

  final VoidCallback onRefresh;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.login_rounded, size: 72, color: AppTheme.primary.withValues(alpha: 0.6)),
          const SizedBox(height: 20),
          Text(
            'سجّل الدخول لعرض الرسائل',
            style: Theme.of(context).textTheme.titleLarge?.copyWith(
                  fontWeight: FontWeight.bold,
                  color: AppTheme.primaryDark,
                ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 8),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 32),
            child: Text(
              'يجب تسجيل الدخول لعرض المحادثات والرسائل',
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: Colors.grey.shade600),
              textAlign: TextAlign.center,
            ),
          ),
          const SizedBox(height: 16),
          TextButton.icon(
            onPressed: onRefresh,
            icon: const Icon(Icons.refresh_rounded, size: 20),
            label: const Text('تحديث'),
          ),
        ],
      ),
    );
  }
}

class _SessionExpiredMessages extends StatelessWidget {
  const _SessionExpiredMessages({required this.onRefresh});

  final VoidCallback onRefresh;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.cloud_off_rounded, size: 72, color: AppTheme.primary.withValues(alpha: 0.6)),
          const SizedBox(height: 20),
          Text(
            'تعذر تحميل الرسائل',
            style: Theme.of(context).textTheme.titleLarge?.copyWith(
                  fontWeight: FontWeight.bold,
                  color: AppTheme.primaryDark,
                ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 8),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 32),
            child: Text(
              'تحقق من الاتصال بالإنترنت، أو سجّل الخروج والدخول مجدداً ثم حدّث',
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: Colors.grey.shade600),
              textAlign: TextAlign.center,
            ),
          ),
          const SizedBox(height: 16),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              TextButton.icon(
                onPressed: () async {
                  await AuthApi.logout();
                  if (context.mounted) {
                    Navigator.of(context).pushAndRemoveUntil(
                      MaterialPageRoute(builder: (_) => const LoginScreen()),
                      (_) => false,
                    );
                  }
                },
                icon: const Icon(Icons.logout_rounded, size: 20),
                label: const Text('تسجيل الخروج'),
              ),
              const SizedBox(width: 12),
              TextButton.icon(
                onPressed: onRefresh,
                icon: const Icon(Icons.refresh_rounded, size: 20),
                label: const Text('تحديث'),
              ),
            ],
          ),
        ],
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

class _NewConversationSheet extends StatefulWidget {
  const _NewConversationSheet({required this.onConversationStarted});

  final void Function(int conversationId, String name) onConversationStarted;

  @override
  State<_NewConversationSheet> createState() => _NewConversationSheetState();
}

class _NewConversationSheetState extends State<_NewConversationSheet> {
  final _searchController = TextEditingController();
  List<MessageUser> _users = [];
  bool _loading = false;
  final _debounce = ValueNotifier<int>(0);

  @override
  void initState() {
    super.initState();
    _searchController.addListener(_onSearchChanged);
  }

  @override
  void dispose() {
    _searchController.removeListener(_onSearchChanged);
    _searchController.dispose();
    super.dispose();
  }

  void _onSearchChanged() {
    _debounce.value++;
    final v = _debounce.value;
    Future.delayed(const Duration(milliseconds: 400), () {
      if (!mounted || _debounce.value != v) return;
      _search();
    });
  }

  Future<void> _search() async {
    final q = _searchController.text.trim();
    if (q.length < 2) {
      setState(() => _users = []);
      return;
    }
    setState(() => _loading = true);
    final users = await MessagesApi.searchUsers(q);
    if (mounted) setState(() {
      _users = users;
      _loading = false;
    });
  }

  Future<void> _startWith(MessageUser user) async {
    final res = await MessagesApi.startConversation(user.id);
    if (res != null && mounted) {
      widget.onConversationStarted(res.conversationId, user.name);
    } else if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('تعذر بدء المحادثة'), behavior: SnackBarBehavior.floating),
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
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text(
            'محادثة جديدة',
            style: Theme.of(context).textTheme.titleLarge?.copyWith(
                  fontWeight: FontWeight.bold,
                  color: AppTheme.primaryDark,
                ),
            textDirection: TextDirection.rtl,
          ),
          const SizedBox(height: 16),
          TextField(
            controller: _searchController,
            decoration: InputDecoration(
              hintText: 'ابحث بالاسم أو البريد...',
              hintTextDirection: TextDirection.rtl,
              prefixIcon: const Icon(Icons.search_rounded),
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
            ),
            textDirection: TextDirection.rtl,
            autofocus: true,
          ),
          const SizedBox(height: 16),
          if (_loading)
            const Padding(
              padding: EdgeInsets.all(24),
              child: Center(child: CircularProgressIndicator(color: AppTheme.primary)),
            )
          else if (_users.isEmpty)
            Padding(
              padding: const EdgeInsets.all(24),
              child: Text(
                _searchController.text.length < 2
                    ? 'اكتب حرفين على الأقل للبحث'
                    : 'لا يوجد مستخدمين',
                style: TextStyle(color: Colors.grey.shade600),
                textAlign: TextAlign.center,
                textDirection: TextDirection.rtl,
              ),
            )
          else
            Flexible(
              child: ListView.builder(
                shrinkWrap: true,
                itemCount: _users.length,
                itemBuilder: (_, i) {
                  final u = _users[i];
                  return ListTile(
                    leading: CircleAvatar(
                      backgroundImage: u.avatar != null && u.avatar!.isNotEmpty
                          ? NetworkImage(u.avatar!)
                          : null,
                      backgroundColor: AppTheme.primary.withValues(alpha: 0.2),
                      child: u.avatar == null || u.avatar!.isEmpty
                          ? Text(
                              u.name.isNotEmpty ? u.name.substring(0, 1).toUpperCase() : '?',
                              style: const TextStyle(color: AppTheme.primary),
                            )
                          : null,
                    ),
                    title: Text(u.name, textDirection: TextDirection.rtl),
                    onTap: () => _startWith(u),
                  );
                },
              ),
            ),
        ],
      ),
    );
  }
}
