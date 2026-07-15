<?php

namespace App\Http\Controllers;

use App\Models\Merchandise;
use App\Models\MerchandiseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminMerchandiseController extends Controller
{
    public function index()
    {
        return view('admin-merchandise.index', [
            'title' => 'Data Merchandise',
            'merchandises' => Merchandise::with('category')->latest()->get(),
        ]);
    }

    public function create()
    {
        return view('admin-merchandise.create', [
            'title' => 'Tambah Merchandise',
            'categories' => MerchandiseCategory::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);

        $image = $request->hasFile('image')
            ? $request->file('image')->store('merchandises', 'public')
            : null;

        Merchandise::create([
            'merchandise_category_id' => $validated['merchandise_category_id'],
            'name' => $validated['name'],
            'slug' => $this->buildSlug($validated['name']),
            'image' => $image,
            'price' => $validated['price'],
            'discount_price' => $validated['discount_price'] ?? null,
            'weight' => $validated['weight'],
            'qty_stock' => $validated['qty_stock'],
            'summary' => $validated['summary'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin-merchandises.index')
            ->with('toast_success', 'Merchandise berhasil disimpan.');
    }

    public function edit(Merchandise $adminMerchandise)
    {
        return view('admin-merchandise.edit', [
            'title' => 'Edit Merchandise',
            'merchandise' => $adminMerchandise,
            'categories' => MerchandiseCategory::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Merchandise $adminMerchandise)
    {
        $validated = $this->validateRequest($request);

        $data = [
            'merchandise_category_id' => $validated['merchandise_category_id'],
            'name' => $validated['name'],
            'slug' => $this->buildSlug($validated['name'], $adminMerchandise->id),
            'price' => $validated['price'],
            'discount_price' => $validated['discount_price'] ?? null,
            'weight' => $validated['weight'],
            'qty_stock' => $validated['qty_stock'],
            'summary' => $validated['summary'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->hasFile('image')) {
            if ($adminMerchandise->image) {
                Storage::disk('public')->delete($adminMerchandise->image);
            }

            $data['image'] = $request->file('image')->store('merchandises', 'public');
        }

        $adminMerchandise->update($data);

        return redirect()->route('admin-merchandises.index')
            ->with('toast_success', 'Merchandise berhasil diperbarui.');
    }

    public function destroy(Merchandise $adminMerchandise)
    {
        if ($adminMerchandise->image) {
            Storage::disk('public')->delete($adminMerchandise->image);
        }

        $adminMerchandise->delete();

        return back()->with('toast_success', 'Merchandise berhasil dihapus.');
    }

    protected function validateRequest(Request $request)
    {
        return $request->validate([
            'merchandise_category_id' => 'required|exists:merchandise_categories,id',
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'weight' => 'required|integer|min:0',
            'qty_stock' => 'required|integer|min:0',
            'summary' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);
    }

    protected function buildSlug($name, $id = null)
    {
        $suffix = $id ?: strtolower(Str::random(4));

        return Str::slug($name) . '-' . $suffix;
    }
}
