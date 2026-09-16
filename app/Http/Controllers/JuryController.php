<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Jury;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class JuryController extends Controller
{
    public function index()
    {
        return view('jury.index', [
            'title' => 'Juri',
            'juries' => Jury::with('category')
                ->orderBy('category_id')
                ->ordered()
                ->get(),
        ]);
    }

    public function create()
    {
        return view('jury.create', [
            'title' => 'Tambah Juri',
            'categories' => Category::active()->orderBy('sort_order')->orderBy('name')->get(),
            'jury' => null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);

        Jury::create([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'title' => $validated['title'] ?? null,
            'photo' => $request->hasFile('photo')
                ? $request->file('photo')->store('juries', 'public')
                : null,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('juries.index')
            ->with('toast_success', 'Juri berhasil disimpan.');
    }

    public function edit(Jury $jury)
    {
        return view('jury.edit', [
            'title' => 'Edit Juri',
            'jury' => $jury,
            'categories' => Category::active()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Jury $jury)
    {
        $validated = $this->validateRequest($request);

        $data = [
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'title' => $validated['title'] ?? null,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->hasFile('photo')) {
            if ($jury->photo && !Str::startsWith($jury->photo, ['http://', 'https://', 'landing/', 'img/', 'assets/'])) {
                Storage::disk('public')->delete($jury->photo);
            }

            $data['photo'] = $request->file('photo')->store('juries', 'public');
        }

        $jury->update($data);

        return redirect()->route('juries.index')
            ->with('toast_success', 'Juri berhasil diperbarui.');
    }

    public function destroy(Jury $jury)
    {
        if ($jury->photo && !Str::startsWith($jury->photo, ['http://', 'https://', 'landing/', 'img/', 'assets/'])) {
            Storage::disk('public')->delete($jury->photo);
        }

        $jury->delete();

        return redirect()->route('juries.index')
            ->with('toast_success', 'Juri berhasil dihapus.');
    }

    protected function validateRequest(Request $request)
    {
        return $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
    }
}