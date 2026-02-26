<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reminder;
use App\Models\ReminderSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReminderSettingsController extends Controller
{
    /**
     * إعدادات التنبيهات حسب النوع
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $settings = ReminderSetting::where('user_id', $user->id)
            ->get()
            ->keyBy('type');

        $types = Reminder::getTypes();
        $result = [];

        foreach ($types as $type => $label) {
            $setting = $settings->get($type);
            $result[] = [
                'type' => $type,
                'label' => $label,
                'icon' => Reminder::getTypeIcon($type),
                'enabled' => $setting ? (bool) $setting->enabled : true,
                'email_enabled' => $setting ? (bool) $setting->email_enabled : true,
            ];
        }

        return response()->json(['data' => $result]);
    }

    /**
     * تحديث إعدادات التنبيهات
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*.type' => ['required', 'string', 'in:' . implode(',', array_keys(Reminder::getTypes()))],
            'settings.*.enabled' => ['sometimes', 'boolean'],
            'settings.*.email_enabled' => ['sometimes', 'boolean'],
        ]);

        $user = $request->user();

        foreach ($validated['settings'] as $item) {
            ReminderSetting::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'type' => $item['type'],
                ],
                [
                    'enabled' => $item['enabled'] ?? true,
                    'email_enabled' => $item['email_enabled'] ?? true,
                ]
            );
        }

        $indexResponse = $this->index($request);
        $data = $indexResponse->getData();

        return response()->json([
            'message' => 'تم تحديث إعدادات التنبيهات.',
            'data' => $data->data ?? [],
        ]);
    }
}
