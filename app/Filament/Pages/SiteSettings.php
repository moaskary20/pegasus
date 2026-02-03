<?php

namespace App\Filament\Pages;

use App\Models\PlatformSetting;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class SiteSettings extends Page
{
    use WithFileUploads;

    protected static ?string $navigationLabel = 'إعدادات الموقع';

    protected static ?string $title = 'إعدادات الموقع';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected string $view = 'filament.pages.site-settings';

    protected static ?string $slug = 'site-settings';

    public static function getNavigationGroup(): ?string
    {
        return 'الإعدادات';
    }

    public static function shouldRegisterNavigation(): bool
    {
        // Moved into PlatformSettings sidebar sections
        return false;
    }

    public string $activeTab = 'slider'; // slider, branding

    public array $settings = [];

    // Site header logo
    public $siteLogoFile = null;

    // Slider form
    public $slideImage = null;

    public array $slideForm = [
        'title' => '',
        'subtitle' => '',
        'primary_text' => 'تصفح الدورات',
        'primary_url' => '/admin/browse-courses',
        'secondary_text' => 'لوحة التحكم',
        'secondary_url' => '/admin',
        'is_active' => true,
    ];

    public ?int $editingSlideIndex = null;

    public function mount(): void
    {
        $this->loadSettings();
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function loadSettings(): void
    {
        $this->settings['site_logo_path'] = (string) PlatformSetting::get('site_logo_path', '');
        $this->settings['site_logo_alt'] = (string) PlatformSetting::get('site_logo_alt', 'Pegasus Academy');

        $slides = PlatformSetting::get('site_home_slider', []);
        $this->settings['site_home_slider'] = is_array($slides) ? $slides : [];
    }

    public function saveSiteLogo(): void
    {
        if (! $this->siteLogoFile) {
            return;
        }

        $this->validate([
            'siteLogoFile' => 'image|max:4096', // 4MB
        ]);

        $oldPath = (string) ($this->settings['site_logo_path'] ?? '');
        $path = $this->siteLogoFile->store('site/branding', 'public');

        if ($oldPath !== '' && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        $this->upsertSetting('site_logo_path', $path, 'site', 'string', 'مسار لوجو الموقع العام (يظهر في هيدر الموقع)');
        $this->settings['site_logo_path'] = $path;
        $this->siteLogoFile = null;

        PlatformSetting::clearGroupCache('site');
        session()->flash('success', 'تم حفظ لوجو الموقع بنجاح');
    }

    public function removeSiteLogo(): void
    {
        $oldPath = (string) ($this->settings['site_logo_path'] ?? '');

        if ($oldPath !== '' && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        $this->upsertSetting('site_logo_path', '', 'site', 'string', 'مسار لوجو الموقع العام (يظهر في هيدر الموقع)');
        $this->settings['site_logo_path'] = '';

        PlatformSetting::clearGroupCache('site');
        session()->flash('success', 'تم حذف لوجو الموقع بنجاح');
    }

    public function saveSiteTextSettings(): void
    {
        $alt = (string) ($this->settings['site_logo_alt'] ?? 'Pegasus Academy');
        $this->upsertSetting('site_logo_alt', $alt, 'site', 'string', 'النص البديل للوجو الخاص بالموقع العام');

        PlatformSetting::clearGroupCache('site');
        session()->flash('success', 'تم حفظ إعدادات الموقع بنجاح');
    }

    public function startAddSlide(): void
    {
        $this->editingSlideIndex = null;
        $this->slideImage = null;
        $this->slideForm = [
            'title' => '',
            'subtitle' => '',
            'primary_text' => 'تصفح الدورات',
            'primary_url' => '/admin/browse-courses',
            'secondary_text' => 'لوحة التحكم',
            'secondary_url' => '/admin',
            'is_active' => true,
        ];
    }

    public function editSlide(int $index): void
    {
        $slides = $this->getSlides();
        if (! isset($slides[$index])) {
            return;
        }

        $slide = $slides[$index];
        $this->editingSlideIndex = $index;
        $this->slideImage = null;
        $this->slideForm = array_merge($this->slideForm, [
            'title' => (string) ($slide['title'] ?? ''),
            'subtitle' => (string) ($slide['subtitle'] ?? ''),
            'primary_text' => (string) ($slide['primary_text'] ?? 'تصفح الدورات'),
            'primary_url' => (string) ($slide['primary_url'] ?? '/admin/browse-courses'),
            'secondary_text' => (string) ($slide['secondary_text'] ?? 'لوحة التحكم'),
            'secondary_url' => (string) ($slide['secondary_url'] ?? '/admin'),
            'is_active' => (bool) ($slide['is_active'] ?? true),
        ]);
    }

    public function saveSlide(): void
    {
        $slides = $this->getSlides();

        $this->validate([
            'slideForm.title' => 'nullable|string|max:120',
            'slideForm.subtitle' => 'nullable|string|max:255',
            'slideForm.primary_text' => 'nullable|string|max:40',
            'slideForm.primary_url' => 'nullable|string|max:255',
            'slideForm.secondary_text' => 'nullable|string|max:40',
            'slideForm.secondary_url' => 'nullable|string|max:255',
            'slideForm.is_active' => 'boolean',
            'slideImage' => ($this->editingSlideIndex === null ? 'required|' : 'nullable|') . 'image|max:6144',
        ]);

        $existingPath = null;
        if ($this->editingSlideIndex !== null && isset($slides[$this->editingSlideIndex])) {
            $existingPath = $slides[$this->editingSlideIndex]['image_path'] ?? null;
        }

        $imagePath = $existingPath;
        if ($this->slideImage) {
            $imagePath = $this->slideImage->store('site/sliders', 'public');

            // delete old image if replacing
            if (is_string($existingPath) && $existingPath !== '' && Storage::disk('public')->exists($existingPath)) {
                Storage::disk('public')->delete($existingPath);
            }
        }

        $newSlide = [
            'image_path' => $imagePath ?: '',
            'title' => (string) ($this->slideForm['title'] ?? ''),
            'subtitle' => (string) ($this->slideForm['subtitle'] ?? ''),
            'primary_text' => (string) ($this->slideForm['primary_text'] ?? ''),
            'primary_url' => (string) ($this->slideForm['primary_url'] ?? ''),
            'secondary_text' => (string) ($this->slideForm['secondary_text'] ?? ''),
            'secondary_url' => (string) ($this->slideForm['secondary_url'] ?? ''),
            'is_active' => (bool) ($this->slideForm['is_active'] ?? true),
        ];

        if ($this->editingSlideIndex === null) {
            $slides[] = $newSlide;
        } else {
            $slides[$this->editingSlideIndex] = $newSlide;
        }

        $this->persistSlides($slides);

        $this->slideImage = null;
        $this->editingSlideIndex = null;

        session()->flash('success', 'تم حفظ الشريحة بنجاح');
    }

    public function deleteSlide(int $index): void
    {
        $slides = $this->getSlides();
        if (! isset($slides[$index])) {
            return;
        }

        $path = $slides[$index]['image_path'] ?? null;
        if (is_string($path) && $path !== '' && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        array_splice($slides, $index, 1);
        $this->persistSlides($slides);

        session()->flash('success', 'تم حذف الشريحة');
    }

    public function moveSlideUp(int $index): void
    {
        $slides = $this->getSlides();
        if ($index <= 0 || ! isset($slides[$index])) {
            return;
        }

        [$slides[$index - 1], $slides[$index]] = [$slides[$index], $slides[$index - 1]];
        $this->persistSlides($slides);
    }

    public function moveSlideDown(int $index): void
    {
        $slides = $this->getSlides();
        if (! isset($slides[$index]) || ! isset($slides[$index + 1])) {
            return;
        }

        [$slides[$index + 1], $slides[$index]] = [$slides[$index], $slides[$index + 1]];
        $this->persistSlides($slides);
    }

    protected function persistSlides(array $slides): void
    {
        $this->settings['site_home_slider'] = array_values($slides);

        $this->upsertSetting('site_home_slider', json_encode($this->settings['site_home_slider'], JSON_UNESCAPED_UNICODE), 'site', 'json', 'شرائح السلايدر في الصفحة الرئيسية (JSON)');

        PlatformSetting::clearGroupCache('site');
        $this->loadSettings();
    }

    protected function getSlides(): array
    {
        $slides = $this->settings['site_home_slider'] ?? [];
        return is_array($slides) ? $slides : [];
    }

    protected function upsertSetting(string $key, string $value, string $group, string $type, string $description): void
    {
        PlatformSetting::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => $group,
                'type' => $type,
                'description' => $description,
            ]
        );
    }
}

