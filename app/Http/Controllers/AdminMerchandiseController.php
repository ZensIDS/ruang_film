<?php

namespace App\Http\Controllers;

use App\Models\Merchandise;
use App\Models\MerchandiseCategory;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminMerchandiseController extends Controller
{
    public function index()
    {
        return view('admin-merchandise.index', [
            'title' => 'Data Merchandise',
            'merchandises' => Merchandise::with(['category', 'activeVariants'])->latest()->get(),
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
        $hasVariants = $request->boolean('has_variants');

        $image = $request->hasFile('image')
            ? $request->file('image')->store('merchandises', 'public')
            : null;

        DB::transaction(function () use ($validated, $request, $image, $hasVariants) {
            $merchandise = Merchandise::create([
                'merchandise_category_id' => $validated['merchandise_category_id'],
                'name' => $validated['name'],
                'slug' => $this->buildSlug($validated['name']),
                'image' => $image,
                'price' => $validated['price'],
                'discount_price' => $validated['discount_price'] ?? null,
                'weight' => $validated['weight'],
                'qty_stock' => $hasVariants ? 0 : $validated['qty_stock'],
                'summary' => $validated['summary'] ?? null,
                'description' => $validated['description'] ?? null,
                'is_active' => $request->boolean('is_active', true),
                'has_variants' => $hasVariants,
            ]);

            if ($hasVariants) {
                $this->syncVariants($merchandise, $validated['variants']);
            }
        });

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
        $hasVariants = $request->boolean('has_variants');

        $data = [
            'merchandise_category_id' => $validated['merchandise_category_id'],
            'name' => $validated['name'],
            'slug' => $this->buildSlug($validated['name'], $adminMerchandise->id),
            'price' => $validated['price'],
            'discount_price' => $validated['discount_price'] ?? null,
            'weight' => $validated['weight'],
            'summary' => $validated['summary'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'has_variants' => $hasVariants,
        ];

        // Produk tanpa varian memakai stok manual; produk bervarian dihitung dari varian
        if (!$hasVariants) {
            $data['qty_stock'] = $validated['qty_stock'];
        }

        if ($request->hasFile('image')) {
            if ($adminMerchandise->image) {
                Storage::disk('public')->delete($adminMerchandise->image);
            }

            $data['image'] = $request->file('image')->store('merchandises', 'public');
        }

        DB::transaction(function () use ($adminMerchandise, $data, $hasVariants, $validated) {
            $adminMerchandise->update($data);

            if ($hasVariants) {
                $this->syncVariants($adminMerchandise, $validated['variants']);
            }
        });

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
        $hasVariants = $request->boolean('has_variants');

        $rules = [
            'merchandise_category_id' => 'required|exists:merchandise_categories,id',
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'weight' => 'required|integer|min:0',
            'summary' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'has_variants' => 'nullable|boolean',
        ];

        if ($hasVariants) {
            $rules['variants'] = 'required|array|min:1|max:30';
            $rules['variants.*.id'] = 'nullable|integer';
            $rules['variants.*.name'] = 'required|string|max:50';
            $rules['variants.*.qty_stock'] = 'required|integer|min:0';
        } else {
            $rules['qty_stock'] = 'required|integer|min:0';
        }

        $validated = $request->validate($rules, [
            'variants.required' => 'Tambahkan minimal satu varian.',
            'variants.min' => 'Tambahkan minimal satu varian.',
            'variants.*.name.required' => 'Nama varian wajib diisi.',
            'variants.*.qty_stock.required' => 'Stok varian wajib diisi.',
            'variants.*.qty_stock.integer' => 'Stok varian harus berupa angka bulat.',
            'variants.*.qty_stock.min' => 'Stok varian tidak boleh negatif.',
        ]);

        if ($hasVariants) {
            $names = collect($validated['variants'])
                ->map(function ($variant) {
                    return mb_strtolower(trim($variant['name']));
                });

            if ($names->count() !== $names->unique()->count()) {
                throw ValidationException::withMessages([
                    'variants' => 'Nama varian tidak boleh sama.',
                ]);
            }
        }

        return $validated;
    }

    /**
     * Samakan varian di database dengan isian form:
     * - baris ber-id    => diperbarui
     * - baris tanpa id  => dibuat baru
     * - baris dihapus   => dihapus, atau diarsipkan (nonaktif) bila pernah dipesan
     */
    protected function syncVariants(Merchandise $merchandise, array $rows)
    {
        $existing = $merchandise->variants()->get()->keyBy('id');
        $keptIds = [];

        foreach (array_values($rows) as $index => $row) {
            $data = [
                'name' => trim($row['name']),
                'qty_stock' => (int) $row['qty_stock'],
                'sort_order' => $index,
                'is_active' => true,
            ];

            $id = isset($row['id']) ? (int) $row['id'] : 0;

            if ($id && $existing->has($id)) {
                $existing[$id]->update($data);
                $keptIds[] = $id;
            } else {
                $keptIds[] = $merchandise->variants()->create($data)->id;
            }
        }

        foreach ($existing as $id => $variant) {
            if (in_array($id, $keptIds, true)) {
                continue;
            }

            if (OrderItem::where('merchandise_variant_id', $id)->exists()) {
                // Pernah dipesan: arsipkan agar riwayat & restock pesanan tetap benar
                $variant->update([
                    'name' => Str::limit($variant->name, 60, '') . ' [arsip-' . $id . ']',
                    'is_active' => false,
                    'sort_order' => 9999,
                ]);
            } else {
                $variant->delete();
            }
        }

        $merchandise->syncStockFromVariants();
    }

    protected function buildSlug($name, $id = null)
    {
        $suffix = $id ?: strtolower(Str::random(4));

        return Str::slug($name) . '-' . $suffix;
    }
}