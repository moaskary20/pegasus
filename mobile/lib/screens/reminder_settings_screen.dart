import 'package:flutter/material.dart';
import '../app_theme.dart';
import '../api/reminder_settings_api.dart';
import 'feature_scaffold.dart';

/// إعدادات التنبيهات — تفعيل/إيقاف حسب نوع التنبيه
class ReminderSettingsScreen extends StatefulWidget {
  const ReminderSettingsScreen({super.key});

  @override
  State<ReminderSettingsScreen> createState() => _ReminderSettingsScreenState();
}

class _ReminderSettingsScreenState extends State<ReminderSettingsScreen> {
  List<ReminderSettingItem> _settings = [];
  bool _loading = true;
  bool _saving = false;

  @override
  void initState() {
    super.initState();
    _loadSettings();
  }

  Future<void> _loadSettings() async {
    setState(() => _loading = true);
    final list = await ReminderSettingsApi.getSettings();
    if (mounted) {
      setState(() {
        _settings = list;
        _loading = false;
      });
    }
  }

  Future<void> _toggle(int index, {bool? enabled, bool? emailEnabled}) async {
    if (_saving) return;
    final s = _settings[index];
    final newEnabled = enabled ?? s.enabled;
    final newEmailEnabled = emailEnabled ?? s.emailEnabled;

    setState(() {
      _settings[index] = ReminderSettingItem(
        type: s.type,
        label: s.label,
        icon: s.icon,
        enabled: newEnabled,
        emailEnabled: newEmailEnabled,
      );
      _saving = true;
    });

    final payload = _settings.map((e) => {
      'type': e.type,
      'enabled': e.enabled,
      'email_enabled': e.emailEnabled,
    }).toList();

    final ok = await ReminderSettingsApi.updateSettings(payload);

    if (mounted) {
      setState(() => _saving = false);
      if (ok) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('تم حفظ الإعدادات'),
            behavior: SnackBarBehavior.floating,
            backgroundColor: Colors.green,
          ),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('حدث خطأ أثناء الحفظ'),
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return FeatureScaffold(
      title: 'إعدادات التنبيهات',
      body: RefreshIndicator(
        onRefresh: _loadSettings,
        color: AppTheme.primary,
        child: _loading
            ? const Center(
                child: Padding(
                  padding: EdgeInsets.all(48),
                  child: CircularProgressIndicator(color: AppTheme.primary),
                ),
              )
            : SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.all(20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'فعّل أو أوقف التنبيهات حسب نوعها',
                      style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                            color: Colors.grey.shade600,
                          ),
                    ),
                    const SizedBox(height: 20),
                    ...List.generate(_settings.length, (i) {
                      final s = _settings[i];
                      return _ReminderSettingTile(
                        icon: s.icon,
                        label: s.label,
                        enabled: s.enabled,
                        emailEnabled: s.emailEnabled,
                        saving: _saving,
                        onEnabledChanged: (v) => _toggle(i, enabled: v),
                        onEmailEnabledChanged: (v) => _toggle(i, emailEnabled: v),
                      );
                    }),
                  ],
                ),
              ),
      ),
    );
  }
}

class _ReminderSettingTile extends StatelessWidget {
  const _ReminderSettingTile({
    required this.icon,
    required this.label,
    required this.enabled,
    required this.emailEnabled,
    required this.saving,
    required this.onEnabledChanged,
    required this.onEmailEnabledChanged,
  });

  final String icon;
  final String label;
  final bool enabled;
  final bool emailEnabled;
  final bool saving;
  final ValueChanged<bool> onEnabledChanged;
  final ValueChanged<bool> onEmailEnabledChanged;

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Text(icon, style: const TextStyle(fontSize: 28)),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    label,
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                          fontWeight: FontWeight.bold,
                          color: AppTheme.primaryDark,
                        ),
                  ),
                ),
                Switch(
                  value: enabled,
                  onChanged: saving ? null : onEnabledChanged,
                  activeColor: AppTheme.primary,
                ),
              ],
            ),
            if (enabled) ...[
              const SizedBox(height: 8),
              Padding(
                padding: const EdgeInsets.only(right: 40),
                child: Row(
                  children: [
                    Expanded(
                      child: Text(
                        'إشعار بالبريد الإلكتروني',
                        style: Theme.of(context).textTheme.bodySmall?.copyWith(
                              color: Colors.grey.shade600,
                            ),
                      ),
                    ),
                    Switch(
                      value: emailEnabled,
                      onChanged: saving ? null : onEmailEnabledChanged,
                      activeColor: AppTheme.primary,
                    ),
                  ],
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
