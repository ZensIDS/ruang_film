<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index()
    {
        return view('faq.index', [
            'title' => 'FAQ',
            'faqs'  => Faq::ordered()->get(),
        ]);
    }

    public function create()
    {
        return view('faq.create', [
            'title' => 'Tambah FAQ',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['is_active'] = $request->boolean('is_active', true);

        Faq::create($validated);

        return redirect()->route('faqs.index')
            ->with('toast_success', 'FAQ berhasil disimpan.');
    }

    public function edit(Faq $faq)
    {
        return view('faq.edit', [
            'title' => 'Edit FAQ',
            'faq'   => $faq,
        ]);
    }

    public function update(Request $request, Faq $faq)
    {
        $validated = $this->validateRequest($request);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['is_active'] = $request->boolean('is_active');

        $faq->update($validated);

        return redirect()->route('faqs.index')
            ->with('toast_success', 'FAQ berhasil diperbarui.');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();

        return redirect()->route('faqs.index')
            ->with('toast_success', 'FAQ berhasil dihapus.');
    }

    protected function validateRequest(Request $request)
    {
        return $request->validate([
            'question'   => 'required|string|max:255',
            'answer'     => 'required|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active'  => 'nullable|boolean',
        ]);
    }
}