<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Film;
use App\Models\SubmissionSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubmissionSettingController extends Controller
{
    public function index()
    {
        $currentSetting = SubmissionSetting::current();

        return view('setting', [
            'title' => 'Setting Submission',
            'setting' => $currentSetting,
            'landingSetting' => $currentSetting,
            'availableFeaturedFilms' => Film::with(['category', 'submissionSetting'])->latest()->get(),
            'defaultFeaturedFilmIds' => $this->defaultFeaturedFilmIds(),
            'defaultLastYearStats' => $this->defaultLastYearStats(),
            'defaultTimelineItems' => SubmissionSetting::defaultTimelineItems(
                optional($currentSetting)->open_at,
                optional($currentSetting)->close_at,
                optional($currentSetting)->display_name
            ),
            'submissionPeriods' => SubmissionSetting::orderByDesc('open_at')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePeriod($request, 'periodForm');

        if (SubmissionSetting::overlaps($validated['open_at'], $validated['close_at'])) {
            return back()->withErrors([
                'open_at' => 'Periode submission tidak boleh tumpang tindih dengan periode lain.',
            ], 'periodForm')->withInput();
        }

        SubmissionSetting::create(array_merge(
            $this->cloneLandingPayload(SubmissionSetting::current()),
            $validated
        ));

        return redirect()->route('settingIndex')
            ->with('success', 'Periode submission berhasil disimpan.');
    }

    public function edit(SubmissionSetting $submissionSetting)
    {
        return view('submission-setting.edit', [
            'title' => 'Edit Periode Submission',
            'submissionSetting' => $submissionSetting,
        ]);
    }

    public function update(Request $request, SubmissionSetting $submissionSetting)
    {
        $validated = $this->validatePeriod($request);

        if (SubmissionSetting::overlaps($validated['open_at'], $validated['close_at'], $submissionSetting->id)) {
            return back()->withErrors([
                'open_at' => 'Periode submission tidak boleh tumpang tindih dengan periode lain.',
            ])->withInput();
        }

        $submissionSetting->update($validated);

        return redirect()->route('settingIndex')
            ->with('success', 'Periode submission berhasil diperbarui.');
    }

    public function updateLanding(Request $request, SubmissionSetting $submissionSetting)
    {
        $validated = $this->validateLanding($request, 'landingForm');
        $oldImages = $this->collectSettingImages($submissionSetting);
        $payload = $this->prepareLandingPayload($request, $validated, $submissionSetting);

        $submissionSetting->update($payload);
        $this->deleteUnusedImages($oldImages, $this->collectImagePathsFromPayload($payload));

        return redirect()->route('settingIndex')
            ->with('success', 'Setting landing page berhasil diperbarui.');
    }

    public function destroy(SubmissionSetting $submissionSetting)
    {
        $images = $this->collectSettingImages($submissionSetting);

        $submissionSetting->delete();

        foreach ($images as $path) {
            $this->deleteStoredImage($path);
        }

        return back()->with('success', 'Periode submission berhasil dihapus.');
    }

    public function updateGeneral(Request $request)
    {
        $validated = $request->validate([
            'payment_due_hours' => 'required|integer|min:1|max:168',
        ]);

        AppSetting::setValue('payment_due_hours', $validated['payment_due_hours']);

        return back()->with('success', 'Setting pembayaran berhasil diperbarui.');
    }

    protected function validatePeriod(Request $request, $bag = null)
    {
        $request->merge($this->normalizePeriodDateTimes($request));

        $rules = [
            'name' => 'required|string|max:255',
            'open_at' => 'required|date',
            'close_at' => 'required|date|after:open_at',
        ];

        $messages = [
            'close_at.after' => 'Waktu penutupan harus setelah waktu pembukaan.',
        ];

        return $bag
            ? $request->validateWithBag($bag, $rules, $messages)
            : $request->validate($rules, $messages);
    }

    protected function normalizePeriodDateTimes(Request $request)
    {
        return [
            'open_at' => $this->combineDateAndTime(
                $request->input('open_at_date'),
                $request->input('open_at_time'),
                $request->input('open_at')
            ),
            'close_at' => $this->combineDateAndTime(
                $request->input('close_at_date'),
                $request->input('close_at_time'),
                $request->input('close_at')
            ),
        ];
    }

    protected function combineDateAndTime($date, $time, $fallback = null)
    {
        if (filled($date) && filled($time)) {
            return trim($date . ' ' . $time);
        }

        return $fallback;
    }

    protected function validateLanding(Request $request, $bag = null)
    {
        $rules = [
            'hero_title' => 'nullable|string|max:255',
            'hero_description' => 'nullable|string',
            'hero_image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            'about_title' => 'nullable|string|max:255',
            'about_description' => 'nullable|string',
            'about_description_secondary' => 'nullable|string',
            'about_image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            'hashtag' => 'nullable|string|max:255',
            'theme_title' => 'nullable|string|max:255',
            'theme_name' => 'nullable|string|max:255',
            'theme_quote' => 'nullable|string',
            'theme_description' => 'nullable|string',
            'theme_image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            'festival_board' => 'nullable|array',
            'festival_board.*.name' => 'nullable|string|max:255',
            'festival_board.*.title' => 'nullable|string|max:255',
            'festival_board_images.*' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            'last_year_title' => 'nullable|string|max:255',
            'last_year_description' => 'nullable|string',
            'last_year_catalog_label' => 'nullable|string|max:255',
            'last_year_catalog_url' => 'nullable|string|max:255',
            'last_year_catalog_file' => 'nullable|file|mimes:pdf|max:51200',
            'last_year_featured_film_ids' => 'nullable|array',
            'last_year_featured_film_ids.*' => 'nullable|integer|exists:films,id',
            'last_year_stat_film_submitted' => 'nullable|integer|min:0',
            'last_year_stat_special_films' => 'nullable|integer|min:0',
            'last_year_stat_audience' => 'nullable|integer|min:0',
            'last_year_stat_participants' => 'nullable|integer|min:0',
            'timeline_items' => 'nullable|array',
            'timeline_items.*.period' => 'nullable|string|max:255',
            'timeline_items.*.title' => 'nullable|string|max:255',
            'timeline_items.*.description' => 'nullable|string',
            'timeline_items.*.icon' => 'nullable|string|max:255',
        ];

        return $bag
            ? $request->validateWithBag($bag, $rules)
            : $request->validate($rules);
    }

    protected function prepareLandingPayload(Request $request, array $validated, SubmissionSetting $submissionSetting = null)
    {
        $payload = $validated;

        $payload['hero_image'] = $this->storeOrKeepImage($request, 'hero_image', optional($submissionSetting)->hero_image);
        $payload['about_image'] = $this->storeOrKeepImage($request, 'about_image', optional($submissionSetting)->about_image);
        $payload['theme_image'] = $this->storeOrKeepImage($request, 'theme_image', optional($submissionSetting)->theme_image);
        $payload['last_year_catalog_file'] = $this->storeOrKeepFile($request, 'last_year_catalog_file', optional($submissionSetting)->last_year_catalog_file, 'landing-settings/catalogs');
        $payload['festival_board'] = $this->buildBoardMembers($request, $submissionSetting);
        $payload['last_year_featured_film_ids'] = $this->buildFeaturedFilmIds($request, $submissionSetting);
        $payload['timeline_items'] = $this->buildTimelineItems($request);
        $payload['last_year_catalog_url'] = array_key_exists('last_year_catalog_url', $validated)
            ? ($request->filled('last_year_catalog_url') ? trim($request->last_year_catalog_url) : null)
            : optional($submissionSetting)->last_year_catalog_url;

        unset($payload['festival_board_images']);

        return $payload;
    }

    protected function cloneLandingPayload(SubmissionSetting $submissionSetting = null)
    {
        $payload = [];

        foreach ($this->landingFields() as $field) {
            if (in_array($field, ['festival_board', 'last_year_featured_film_ids', 'timeline_items'], true)) {
                $payload[$field] = $submissionSetting ? ($submissionSetting->festival_board ?: []) : [];
                if ($field === 'last_year_featured_film_ids') {
                    $payload[$field] = $submissionSetting ? ($submissionSetting->last_year_featured_film_ids ?: []) : [];
                }
                if ($field === 'timeline_items') {
                    $payload[$field] = $submissionSetting
                        ? ($submissionSetting->timeline_items ?: SubmissionSetting::defaultTimelineItems(
                            $submissionSetting->open_at,
                            $submissionSetting->close_at,
                            $submissionSetting->display_name
                        ))
                        : [];
                }
                continue;
            }

            $payload[$field] = optional($submissionSetting)->{$field};
        }

        return $payload;
    }

    protected function landingFields()
    {
        return [
            'hero_title',
            'hero_description',
            'hero_image',
            'about_title',
            'about_description',
            'about_description_secondary',
            'about_image',
            'hashtag',
            'theme_title',
            'theme_name',
            'theme_quote',
            'theme_description',
            'theme_image',
            'festival_board',
            'last_year_title',
            'last_year_description',
            'last_year_catalog_label',
            'last_year_catalog_url',
            'last_year_catalog_file',
            'last_year_featured_film_ids',
            'last_year_stat_film_submitted',
            'last_year_stat_special_films',
            'last_year_stat_audience',
            'last_year_stat_participants',
            'timeline_items',
        ];
    }

    protected function buildBoardMembers(Request $request, SubmissionSetting $submissionSetting = null)
    {
        $existingMembers = collect(optional($submissionSetting)->festival_board ?: []);
        $inputMembers = collect($request->input('festival_board', []));
        $uploadedImages = $request->file('festival_board_images', []);

        $members = [];

        foreach ($inputMembers as $index => $currentMember) {
            $name = trim((string) ($currentMember['name'] ?? ''));
            $title = trim((string) ($currentMember['title'] ?? ''));
            $image = data_get($existingMembers->get($index), 'image');

            if (isset($uploadedImages[$index])) {
                $image = $uploadedImages[$index]->store('landing-settings/board', 'public');
            }

            if ($name !== '' || $title !== '' || $image) {
                $members[] = [
                    'name' => $name,
                    'title' => $title,
                    'image' => $image,
                ];
            }
        }

        return array_values($members);
    }

    protected function storeOrKeepImage(Request $request, $field, $existingPath = null)
    {
        return $this->storeOrKeepFile($request, $field, $existingPath, 'landing-settings');
    }

    protected function storeOrKeepFile(Request $request, $field, $existingPath = null, $directory = 'landing-settings')
    {
        if ($request->hasFile($field)) {
            return $request->file($field)->store($directory, 'public');
        }

        return $existingPath;
    }

    protected function collectSettingImages(SubmissionSetting $submissionSetting)
    {
        $boardImages = collect($submissionSetting->festival_board ?: [])
            ->pluck('image')
            ->filter()
            ->values()
            ->all();

        return array_values(array_filter(array_merge([
            $submissionSetting->hero_image,
            $submissionSetting->about_image,
            $submissionSetting->theme_image,
            $submissionSetting->last_year_catalog_file,
        ], $boardImages)));
    }

    protected function collectImagePathsFromPayload(array $payload)
    {
        $boardImages = collect($payload['festival_board'] ?? [])
            ->pluck('image')
            ->filter()
            ->values()
            ->all();

        return array_values(array_filter(array_merge([
            $payload['hero_image'] ?? null,
            $payload['about_image'] ?? null,
            $payload['theme_image'] ?? null,
            $payload['last_year_catalog_file'] ?? null,
        ], $boardImages)));
    }

    protected function deleteUnusedImages(array $oldImages, array $newImages)
    {
        foreach (array_diff($oldImages, $newImages) as $path) {
            $this->deleteStoredImage($path);
        }
    }

    protected function deleteStoredImage($path)
    {
        if (
            $path
            && !preg_match('/^(https?:\/\/|landing\/|img\/|assets\/)/', $path)
            && !$this->isSubmissionImageStillUsed($path)
        ) {
            Storage::disk('public')->delete($path);
        }
    }

    protected function isSubmissionImageStillUsed($path)
    {
        return SubmissionSetting::query()
            ->where(function ($query) use ($path) {
                $query->where('hero_image', $path)
                    ->orWhere('about_image', $path)
                    ->orWhere('theme_image', $path)
                    ->orWhere('last_year_catalog_file', $path)
                    ->orWhere('festival_board', 'like', '%' . $path . '%');
            })
            ->exists();
    }

    protected function buildFeaturedFilmIds(Request $request, SubmissionSetting $submissionSetting = null)
    {
        return collect($request->input('last_year_featured_film_ids', optional($submissionSetting)->last_year_featured_film_ids ?: []))
            ->filter(function ($filmId) {
                return filled($filmId);
            })
            ->map(function ($filmId) {
                return (int) $filmId;
            })
            ->unique()
            ->values()
            ->all();
    }

    protected function buildTimelineItems(Request $request)
    {
        return collect($request->input('timeline_items', []))
            ->map(function ($item) {
                return [
                    'period' => trim((string) data_get($item, 'period')),
                    'title' => trim((string) data_get($item, 'title')),
                    'description' => trim((string) data_get($item, 'description')),
                    'icon' => trim((string) data_get($item, 'icon')),
                ];
            })
            ->filter(function ($item) {
                return filled($item['period'])
                    || filled($item['title'])
                    || filled($item['description'])
                    || filled($item['icon']);
            })
            ->values()
            ->all();
    }

    protected function completedPeriod()
    {
        return SubmissionSetting::where('close_at', '<', now())
            ->orderByDesc('close_at')
            ->first();
    }

    protected function defaultFeaturedFilmIds()
    {
        $completedPeriod = $this->completedPeriod();

        if (!$completedPeriod) {
            return [];
        }

        return Film::sortCollectionByWinnerRank(
            Film::query()
                ->where('submission_setting_id', $completedPeriod->id)
                ->whereNotNull('poster')
                ->get()
        )->take(6)
            ->pluck('id')
            ->values()
            ->all();
    }

    protected function defaultLastYearStats()
    {
        $completedPeriod = $this->completedPeriod();

        if (!$completedPeriod) {
            return [
                'last_year_stat_film_submitted' => 0,
                'last_year_stat_special_films' => 0,
                'last_year_stat_audience' => 0,
                'last_year_stat_participants' => 0,
            ];
        }

        return [
            'last_year_stat_film_submitted' => Film::where('submission_setting_id', $completedPeriod->id)->count(),
            'last_year_stat_special_films' => Film::where('submission_setting_id', $completedPeriod->id)
                ->whereIn('curation_status', [Film::CURATION_APPROVED, Film::CURATION_REJECTED])
                ->count(),
            'last_year_stat_audience' => 0,
            'last_year_stat_participants' => Film::where('submission_setting_id', $completedPeriod->id)
                ->distinct()
                ->count('user_id'),
        ];
    }
}
