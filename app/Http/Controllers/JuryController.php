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
            'title'  => 'Juri & Kurator',
            'juries' => Jury::with('category')
                ->orderByRaw("type = 'kurator'") // juri dulu, baru kurator
                ->orderBy('category_id')
                ->ordered()
                ->get(),
        ]);
    }

    public function create(Request $request)
    {
        return view('jury.create', [
            'title'      => 'Tambah Juri / Kurator',
            'type'       => $this->resolveType($request->get('type')),
            'categories' => Category::active()->orderBy('sort_order')->orderBy('name')->get(),
            'jury'       => null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);

        Jury::create([
            'type'        => $validated['type'],
            'category_id' => $validated['type'] === Jury::TYPE_JURI ? $validated['category_id'] : null,
            'name'        => $validated['name'],
            'title'       => $validated['title'] ?? null,
            'photo'       => $request->hasFile('photo')
                ? $request->file('photo')->store('juries', 'public')
                : null,
            'sort_order'  => (int) ($validated['sort_order'] ?? 0),
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return redirect()->route('juries.index')
            ->with('toast_success', $validated['type'] === Jury::TYPE_KURATOR
                ? 'Kurator berhasil disimpan.'
                : 'Juri berhasil disimpan.');
    }

    public function edit(Jury $jury)
    {
        return view('jury.edit', [
            'title'      => 'Edit Juri / Kurator',
            'jury'       => $jury,
            'categories' => Category::active()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Jury $jury)
    {
        $validated = $this->validateRequest($request);

        $data = [
            'type'        => $validated['type'],
            'category_id' => $validated['type'] === Jury::TYPE_JURI ? $validated['category_id'] : null,
            'name'        => $validated['name'],
            'title'       => $validated['title'] ?? null,
            'sort_order'  => (int) ($validated['sort_order'] ?? 0),
            'is_active'   => $request->boolean('is_active'),
        ];

        if ($request->hasFile('photo')) {
            if ($jury->photo && !Str::startsWith($jury->photo, ['http://', 'https://', 'landing/', 'img/', 'assets/'])) {
                Storage::disk('public')->delete($jury->photo);
            }

            $data['photo'] = $request->file('photo')->store('juries', 'public');
        }

        $jury->update($data);

        return redirect()->route('juries.index')
            ->with('toast_success', $validated['type'] === Jury::TYPE_KURATOR
                ? 'Kurator berhasil diperbarui.'
                : 'Juri berhasil diperbarui.');
    }

    public function destroy(Jury $jury)
    {
        if ($jury->photo && !Str::startsWith($jury->photo, ['http://', 'https://', 'landing/', 'img/', 'assets/'])) {
            Storage::disk('public')->delete($jury->photo);
        }

        $jury->delete();

        return redirect()->route('juries.index')
            ->with('toast_success', $jury->type === Jury::TYPE_KURATOR
                ? 'Kurator berhasil dihapus.'
                : 'Juri berhasil dihapus.');
    }

    protected function resolveType($type)
    {
        return $type === Jury::TYPE_KURATOR ? Jury::TYPE_KURATOR : Jury::TYPE_JURI;
    }

    protected function validateRequest(Request $request)
    {
        return $request->validate([
            'type'        => 'required|in:juri,kurator',
            'category_id' => 'required_if:type,juri|nullable|exists:categories,id',
            'name'        => 'required|string|max:255',
            'title'       => 'nullable|string|max:255',
            'photo'       => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            'sort_order'  => 'nullable|integer|min:0',
            'is_active'   => 'nullable|boolean',
        ]);
    }
}