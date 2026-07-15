<?php

namespace App\Http\Controllers;

use App\Models\MerchandiseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MerchandiseCategoryController extends Controller
{
    public function index()
    {
        return view('merchandise-category.index', [
            'title' => 'Kategori Merchandise',
            'categories' => MerchandiseCategory::latest()->get(),
        ]);
    }

    public function create()
    {
        return view('merchandise-category.create', [
            'title' => 'Tambah Kategori Merchandise',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        MerchandiseCategory::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . strtolower(Str::random(4)),
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('merchandise-categories.index')
            ->with('toast_success', 'Kategori merchandise berhasil disimpan.');
    }

    public function edit(MerchandiseCategory $merchandiseCategory)
    {
        return view('merchandise-category.edit', [
            'title' => 'Edit Kategori Merchandise',
            'category' => $merchandiseCategory,
        ]);
    }

    public function update(Request $request, MerchandiseCategory $merchandiseCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $merchandiseCategory->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . $merchandiseCategory->id,
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('merchandise-categories.index')
            ->with('toast_success', 'Kategori merchandise berhasil diperbarui.');
    }

    public function destroy(MerchandiseCategory $merchandiseCategory)
    {
        $merchandiseCategory->delete();

        return back()->with('toast_success', 'Kategori merchandise berhasil dihapus.');
    }
}
