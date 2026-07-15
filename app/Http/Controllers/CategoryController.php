<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ReviewRubric;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('category.index', [
            'title' => 'List Kategori',
            'categories' => Category::orderBy('sort_order')->orderBy('name')->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('category.create', [
            'title' => 'Tambah Kategori',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $this->validateCategory($request);
        $rubrics = $this->validateRubrics($request);

        if ($request->hasFile('image')) {
            $validatedData['image'] = $request->file('image')->store('categories', 'public');
        }

        $validatedData['slug'] = $validatedData['slug'] ?: Str::slug($validatedData['name']);
        $validatedData['is_active'] = $request->boolean('is_active');
        $validatedData['sort_order'] = (int) ($validatedData['sort_order'] ?? 0);

        $category = Category::create($validatedData);
        $this->syncRubrics($category, $rubrics);

        return redirect(route('categories.index'))->with('toast_success', 'Berhasil Menyimpan Data!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function edit(Category $category)
    {
        $category->load('rubrics.groups.items');

        return view('category.edit', [
            'title' => 'Edit Kategori',
            'categories' => $category,
            'category' => $category,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        $validatedData = $this->validateCategory($request, $category);
        $rubrics = $this->validateRubrics($request);

        if ($request->hasFile('image')) {
            if ($category->image && !Str::startsWith($category->image, ['http://', 'https://', 'landing/', 'img/', 'assets/'])) {
                Storage::disk('public')->delete($category->image);
            }

            $validatedData['image'] = $request->file('image')->store('categories', 'public');
        }

        $validatedData['slug'] = $validatedData['slug'] ?: Str::slug($validatedData['name']);
        $validatedData['is_active'] = $request->boolean('is_active');
        $validatedData['sort_order'] = (int) ($validatedData['sort_order'] ?? 0);

        $category->update($validatedData);
        $this->syncRubrics($category, $rubrics);

        return redirect(route('categories.index'))->with('toast_success', 'Berhasil Menyimpan Data!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        if ($category->image && !Str::startsWith($category->image, ['http://', 'https://', 'landing/', 'img/', 'assets/'])) {
            Storage::disk('public')->delete($category->image);
        }

        Category::destroy($category->id);
        return redirect(route('categories.index'))->with('toast_success', 'Berhasil Menghapus Data!');
    }

    protected function validateCategory(Request $request, Category $category = null)
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . optional($category)->id,
            'description' => 'nullable|string',
            'landing_summary' => 'nullable|string',
            'detail_route' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);
    }

    protected function validateRubrics(Request $request)
    {
        $validated = $request->validate([
            'rubrics' => 'nullable|array',
            'rubrics.*.present' => 'nullable',
            'rubrics.*.groups' => 'nullable|array',
            'rubrics.*.groups.*.title' => 'nullable|string|max:255',
            'rubrics.*.groups.*.weight' => 'nullable|numeric|min:0',
            'rubrics.*.groups.*.sort_order' => 'nullable|integer|min:0',
            'rubrics.*.groups.*.items' => 'nullable|array',
            'rubrics.*.groups.*.items.*.title' => 'nullable|string|max:255',
            'rubrics.*.groups.*.items.*.description' => 'nullable|string',
            'rubrics.*.groups.*.items.*.weight' => 'nullable|numeric|min:0',
            'rubrics.*.groups.*.items.*.sort_order' => 'nullable|integer|min:0',
        ]);

        $rubrics = $validated['rubrics'] ?? [];
        $invalidStages = array_diff(array_keys($rubrics), ReviewRubric::stages());

        if ($invalidStages) {
            throw ValidationException::withMessages([
                'rubrics' => 'Tahap rubrik tidak valid.',
            ]);
        }

        return $rubrics;
    }

    protected function syncRubrics(Category $category, array $rubrics)
    {
        foreach (ReviewRubric::stages() as $stage) {
            if (!array_key_exists($stage, $rubrics)) {
                continue;
            }

            $groups = $this->normalizeRubricGroups($rubrics[$stage]['groups'] ?? []);

            if (empty($groups)) {
                optional($category->rubrics()->forStage($stage)->first())->delete();
                continue;
            }

            $rubric = ReviewRubric::updateOrCreate(
                [
                    'category_id' => $category->id,
                    'stage' => $stage,
                ],
                [
                    'name' => $category->name . ' - ' . (ReviewRubric::stageLabels()[$stage] ?? ucfirst($stage)),
                    'is_active' => true,
                ]
            );

            $rubric->groups()->delete();

            foreach ($groups as $groupIndex => $groupData) {
                $group = $rubric->groups()->create([
                    'title' => $groupData['title'],
                    'weight' => $groupData['weight'],
                    'sort_order' => $groupData['sort_order'] ?? $groupIndex,
                ]);

                foreach ($groupData['items'] as $itemIndex => $itemData) {
                    $group->items()->create([
                        'title' => $itemData['title'],
                        'description' => $itemData['description'],
                        'weight' => $itemData['weight'],
                        'sort_order' => $itemData['sort_order'] ?? $itemIndex,
                    ]);
                }
            }
        }
    }

    protected function normalizeRubricGroups(array $groups)
    {
        $normalizedGroups = [];

        foreach (array_values($groups) as $groupIndex => $groupData) {
            $groupTitle = trim((string) ($groupData['title'] ?? ''));
            $items = $this->normalizeRubricItems($groupData['items'] ?? []);

            if ($groupTitle === '' && empty($items)) {
                continue;
            }

            if ($groupTitle === '') {
                throw ValidationException::withMessages([
                    'rubrics' => 'Judul aspek wajib diisi jika memiliki sub-aspek.',
                ]);
            }

            if (empty($items)) {
                continue;
            }

            $normalizedGroups[] = [
                'title' => $groupTitle,
                'weight' => $this->nullableNumericValue($groupData, 'weight'),
                'sort_order' => $groupData['sort_order'] ?? $groupIndex,
                'items' => $items,
            ];
        }

        return $normalizedGroups;
    }

    protected function normalizeRubricItems(array $items)
    {
        $normalizedItems = [];

        foreach (array_values($items) as $itemIndex => $itemData) {
            $itemTitle = trim((string) ($itemData['title'] ?? ''));
            $description = trim((string) ($itemData['description'] ?? ''));

            if ($itemTitle === '' && $description === '') {
                continue;
            }

            if ($itemTitle === '') {
                throw ValidationException::withMessages([
                    'rubrics' => 'Judul sub-aspek wajib diisi.',
                ]);
            }

            $normalizedItems[] = [
                'title' => $itemTitle,
                'description' => $description ?: null,
                'weight' => $this->nullableNumericValue($itemData, 'weight', 1),
                'sort_order' => $itemData['sort_order'] ?? $itemIndex,
            ];
        }

        return $normalizedItems;
    }

    protected function nullableNumericValue(array $data, $key, $default = null)
    {
        if (!array_key_exists($key, $data) || $data[$key] === '' || $data[$key] === null) {
            return $default;
        }

        return $data[$key];
    }
}
