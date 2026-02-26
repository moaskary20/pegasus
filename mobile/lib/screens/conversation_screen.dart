import 'package:flutter/material.dart';
import '../api/messages_api.dart';
import '../api/config.dart';
import '../app_theme.dart';
import 'feature_scaffold.dart';

/// شاشة المحادثة — عرض الرسائل وإرسال جديدة
class ConversationScreen extends StatefulWidget {
  const ConversationScreen({
    super.key,
    required this.conversationId,
    this.conversationName,
  });

  final int conversationId;
  final String? conversationName;

  @override
  State<ConversationScreen> createState() => _ConversationScreenState();
}

class _ConversationScreenState extends State<ConversationScreen> {
  ConversationDetail? _data;
  bool _loading = true;
  String? _error;
  final _messageController = TextEditingController();
  final _scrollController = ScrollController();
  bool _sending = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _messageController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    final res = await MessagesApi.getConversation(widget.conversationId);
    if (!mounted) return;
    setState(() {
      _data = res;
      _loading = res == null;
      _error = res == null ? 'تعذر تحميل المحادثة' : null;
    });
    if (res != null && res.messages.isNotEmpty) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        _scrollController.animateTo(
          _scrollController.position.maxScrollExtent,
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeOut,
        );
      });
    }
  }

  Future<void> _send() async {
    final text = _messageController.text.trim();
    if (text.isEmpty || _sending) return;
    setState(() => _sending = true);
    _messageController.clear();
    final res = await MessagesApi.sendMessage(widget.conversationId, text);
    if (!mounted) return;
    setState(() => _sending = false);
    if (res != null) {
      _data = ConversationDetail(
        conversation: _data!.conversation,
        messages: [..._data!.messages, res.message],
      );
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (_scrollController.hasClients) {
          _scrollController.animateTo(
            _scrollController.position.maxScrollExtent,
            duration: const Duration(milliseconds: 200),
            curve: Curves.easeOut,
          );
        }
      });
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('تعذر إرسال الرسالة'), behavior: SnackBarBehavior.floating),
      );
    }
  }

  String _fullUrl(String? path) {
    if (path == null || path.isEmpty) return '';
    if (path.startsWith('http')) return path;
    final base = apiBaseUrl.endsWith('/') ? apiBaseUrl : '$apiBaseUrl/';
    return path.startsWith('/') ? '$base${path.substring(1)}' : '$base$path';
  }

  @override
  Widget build(BuildContext context) {
    final name = widget.conversationName ?? _data?.conversation.name ?? 'محادثة';
    return FeatureScaffold(
      title: name,
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: AppTheme.primary))
          : _error != null
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.error_outline_rounded, size: 64, color: Colors.grey.shade400),
                      const SizedBox(height: 16),
                      Text(_error!, style: Theme.of(context).textTheme.titleMedium, textDirection: TextDirection.rtl),
                      const SizedBox(height: 24),
                      FilledButton(
                        onPressed: _load,
                        style: FilledButton.styleFrom(backgroundColor: AppTheme.primary),
                        child: const Text('إعادة المحاولة'),
                      ),
                    ],
                  ),
                )
              : Column(
                  children: [
                    Expanded(
                      child: RefreshIndicator(
                        onRefresh: _load,
                        child: ListView.builder(
                          controller: _scrollController,
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                          itemCount: _data!.messages.length,
                          itemBuilder: (_, i) => _MessageBubble(
                            message: _data!.messages[i],
                            avatarUrl: _data!.messages[i].isMine
                                ? null
                                : (_data!.conversation.otherUser?.avatar != null
                                    ? _fullUrl(_data!.conversation.otherUser!.avatar)
                                    : null),
                          ),
                        ),
                      ),
                    ),
                    Container(
                      padding: EdgeInsets.fromLTRB(16, 12, 16, 12 + MediaQuery.of(context).padding.bottom),
                      color: Colors.white,
                      child: SafeArea(
                        child: Row(
                          textDirection: TextDirection.rtl,
                          children: [
                            Expanded(
                              child: TextField(
                                controller: _messageController,
                                decoration: InputDecoration(
                                  hintText: 'اكتب رسالة...',
                                  hintTextDirection: TextDirection.rtl,
                                  border: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(24),
                                    borderSide: BorderSide.none,
                                  ),
                                  filled: true,
                                  fillColor: Colors.grey.shade100,
                                  contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                                ),
                                textDirection: TextDirection.rtl,
                                maxLines: 3,
                                minLines: 1,
                                onSubmitted: (_) => _send(),
                              ),
                            ),
                            const SizedBox(width: 8),
                            CircleAvatar(
                              backgroundColor: AppTheme.primary,
                              child: IconButton(
                                icon: _sending
                                    ? const SizedBox(
                                        width: 24,
                                        height: 24,
                                        child: CircularProgressIndicator(
                                          strokeWidth: 2,
                                          color: Colors.white,
                                        ),
                                      )
                                    : const Icon(Icons.send_rounded, color: Colors.white),
                                onPressed: _sending ? null : _send,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
    );
  }
}

class _MessageBubble extends StatelessWidget {
  const _MessageBubble({required this.message, this.avatarUrl});

  final ChatMessage message;
  final String? avatarUrl;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        textDirection: message.isMine ? TextDirection.ltr : TextDirection.rtl,
        mainAxisAlignment: message.isMine ? MainAxisAlignment.end : MainAxisAlignment.start,
        crossAxisAlignment: CrossAxisAlignment.end,
        children: [
          if (!message.isMine && avatarUrl != null)
            CircleAvatar(
              radius: 18,
              backgroundImage: NetworkImage(avatarUrl!),
              backgroundColor: AppTheme.primary.withValues(alpha: 0.2),
            ),
          if (!message.isMine && avatarUrl == null)
            CircleAvatar(
              radius: 18,
              backgroundColor: AppTheme.primary.withValues(alpha: 0.2),
              child: Text(
                (message.senderName ?? '?').isNotEmpty ? (message.senderName!.substring(0, 1)).toUpperCase() : '?',
                style: const TextStyle(color: AppTheme.primary, fontWeight: FontWeight.bold),
              ),
            ),
          if (!message.isMine) const SizedBox(width: 8),
          Flexible(
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
              decoration: BoxDecoration(
                color: message.isMine ? AppTheme.primary : Colors.grey.shade200,
                borderRadius: BorderRadius.only(
                  topLeft: const Radius.circular(18),
                  topRight: const Radius.circular(18),
                  bottomLeft: Radius.circular(message.isMine ? 18 : 4),
                  bottomRight: Radius.circular(message.isMine ? 4 : 18),
                ),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (message.body.isNotEmpty)
                    Text(
                      message.body,
                      style: TextStyle(
                        color: message.isMine ? Colors.white : Colors.grey.shade800,
                        fontSize: 15,
                      ),
                      textDirection: TextDirection.rtl,
                    ),
                  const SizedBox(height: 4),
                  Text(
                    _formatTime(message.createdAt),
                    style: TextStyle(
                      color: message.isMine ? Colors.white70 : Colors.grey.shade600,
                      fontSize: 11,
                    ),
                  ),
                ],
              ),
            ),
          ),
          if (message.isMine) const SizedBox(width: 8),
        ],
      ),
    );
  }

  String _formatTime(String iso) {
    try {
      final dt = DateTime.tryParse(iso);
      if (dt == null) return iso;
      final now = DateTime.now();
      if (dt.day == now.day && dt.month == now.month && dt.year == now.year) {
        return '${dt.hour.toString().padLeft(2, '0')}:${dt.minute.toString().padLeft(2, '0')}';
      }
      return '${dt.day}/${dt.month}';
    } catch (_) {
      return iso;
    }
  }
}
