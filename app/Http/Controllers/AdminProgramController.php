<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\ProgramCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminProgramController extends Controller
{
    public function index()
    {
        return view('admin-program.index', [
            'title' => 'Program Festival',
            'programs' => Program::with('category')->ordered()->get(),
        ]);
    }

    public function create()
    {
        return view('admin-program.create', [
            'title' => 'Tambah Program',
            'categories' => ProgramCategory::ordered()->get(),
            'program' => null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);

        $program = Program::create([
            'program_category_id' => $validated['program_category_id'],
            'title' => $validated['title'],
            'slug' => $this->resolveSlug($validated['title'], $validated['slug'] ?? null),
            'summary' => $validated['summary'] ?? null,
            'content' => $validated['content'] ?? null,
            'poster' => $request->hasFile('poster')
                ? $request->file('poster')->store('programs', 'public')
                : null,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin-programs.index')
            ->with('toast_success', 'Program berhasil disimpan.');
    }

    public function edit(Program $adminProgram)
    {
        return view('admin-program.edit', [
            'title' => 'Edit Program',
            'program' => $adminProgram,
            'categories' => ProgramCategory::ordered()->get(),
        ]);
    }

    public function update(Request $request, Program $adminProgram)
    {
        $validated = $this->validateRequest($request);

        $data = [
            'program_category_id' => $validated['program_category_id'],
            'title' => $validated['title'],
            'slug' => $this->resolveSlug($validated['title'], $validated['slug'] ?? null, $adminProgram->id),
            'summary' => $validated['summary'] ?? null,
            'content' => $validated['content'] ?? null,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->hasFile('poster')) {
            if ($adminProgram->poster && !Str::startsWith($adminProgram->poster, ['http://', 'https://', 'landing/', 'img/', 'assets/'])) {
                Storage::disk('public')->delete($adminProgram->poster);
            }

            $data['poster'] = $request->file('poster')->store('programs', 'public');
        }

        $adminProgram->update($data);

        return redirect()->route('admin-programs.index')
            ->with('toast_success', 'Program berhasil diperbarui.');
    }

    public function destroy(Program $adminProgram)
    {
        if ($adminProgram->poster && !Str::startsWith($adminProgram->poster, ['http://', 'https://', 'landing/', 'img/', 'assets/'])) {
            Storage::disk('public')->delete($adminProgram->poster);
        }

        $adminProgram->delete();

        return redirect()->route('admin-programs.index')
            ->with('toast_success', 'Program berhasil dihapus.');
    }

    protected function validateRequest(Request $request)
    {
        return $request->validate([
            'program_category_id' => 'required|exists:program_categories,id',
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'summary' => 'nullable|string',
            'content' => 'nullable|string',
            'poster' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
    }

    protected function resolveSlug($title, $slug = null, $ignoreId = null)
    {
        $baseSlug = Str::slug($slug ?: $title);
        $baseSlug = $baseSlug ?: 'program';
        $candidate = $baseSlug;
        $counter = 2;

        while (
            Program::where('slug', $candidate)
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
