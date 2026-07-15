<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\ProgramCategory;
use Illuminate\Http\Request;

class LandingProgramController extends Controller
{
    public function index(Request $request)
    {
        $programCategories = ProgramCategory::active()->ordered()->get();
        $selectedCategory = null;

        $programs = Program::with('category')
            ->active()
            ->whereHas('category', function ($query) {
                $query->active();
            });

        if ($request->filled('category')) {
            $selectedCategory = $programCategories->firstWhere('slug', $request->category);

            if ($selectedCategory) {
                $programs->where('program_category_id', $selectedCategory->id);
            } else {
                $programs->whereRaw('1 = 0');
            }
        }

        return view('landing.programs.index', [
            'programs' => $programs->ordered()->paginate(12)->withQueryString(),
            'programCategories' => $programCategories,
            'filters' => $request->only('category'),
            'selectedProgramCategory' => $selectedCategory,
        ]);
    }

    public function show(Program $program)
    {
        $program->load('category');

        abort_unless(
            $program->is_active && optional($program->category)->is_active,
            404
        );

        $relatedPrograms = Program::with('category')
            ->active()
            ->whereHas('category', function ($query) use ($program) {
                $query->active()->where('id', optional($program->category)->id);
            })
            ->where('id', '!=', $program->id)
            ->ordered()
            ->take(3)
            ->get();

        return view('landing.programs.show', [
            'program' => $program,
            'relatedPrograms' => $relatedPrograms,
        ]);
    }
}
