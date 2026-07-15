<?php

namespace App\Http\Controllers;

use App\Models\ProgramCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProgramCategoryController extends Controller
{
    public function index()
    {
        return view('program-category.index', [
            'title' => 'Kategori Program',
            'categories' => ProgramCategory::ordered()->get(),
        ]);
    }

    public function create()
    {
        return view('program-category.create', [
            'title' => 'Tambah Kategori Program',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);
        $validated['slug'] = $this->resolveSlug($validated['name'], $validated['slug'] ?? null);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['is_active'] = $request->boolean('is_active', true);

        ProgramCategory::create($validated);

        return redirect()->route('program-categories.index')
            ->with('toast_success', 'Kategori program berhasil disimpan.');
    }

    public function edit(ProgramCategory $programCategory)
    {
        return view('program-category.edit', [
            'title' => 'Edit Kategori Program',
            'category' => $programCategory,
        ]);
    }

    public function update(Request $request, ProgramCategory $programCategory)
    {
        $validated = $this->validateRequest($request);
        $validated['slug'] = $this->resolveSlug($validated['name'], $validated['slug'] ?? null, $programCategory->id);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['is_active'] = $request->boolean('is_active');

        $programCategory->update($validated);

        return redirect()->route('program-categories.index')
            ->with('toast_success', 'Kategori program berhasil diperbarui.');
    }

    public function destroy(ProgramCategory $programCategory)
    {
        $programCategory->programs()->get()->each(function ($program) {
            if ($program->poster && !Str::startsWith($program->poster, ['http://', 'https://', 'landing/', 'img/', 'assets/'])) {
                Storage::disk('public')->delete($program->poster);
            }
        });

        $programCategory->delete();

        return redirect()->route('program-categories.index')
            ->with('toast_success', 'Kategori program berhasil dihapus.');
    }

    protected function validateRequest(Request $request)
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
    }

    protected function resolveSlug($name, $slug = null, $ignoreId = null)
    {
        $baseSlug = Str::slug($slug ?: $name);
        $baseSlug = $baseSlug ?: 'program-category';
        $candidate = $baseSlug;
        $counter = 2;

        while (
            ProgramCategory::where('slug', $candidate)
                ->when($ignoreId, function ($query) use ($ignoreId) {
                    $query->where('id', '!=', $ignoreId);
                })
                ->exists()
        ) {
            $candidate = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $candidate;
    }
}
