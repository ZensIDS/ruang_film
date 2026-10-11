<?php

namespace App\Http\Controllers;

use App\Models\Bioskop;
use Illuminate\Http\Request;

class BioskopController extends Controller
{
    public function index()
    {
        return view('bioskop.index', [
            'title'    => 'Bioskop Gratis',
            'bioskops' => Bioskop::ordered()->get(),
        ]);
    }

    public function create()
    {
        return view('bioskop.create', [
            'title' => 'Tambah Bioskop Gratis',
        ]);
    }

    public function store(Request $request)
    {
        Bioskop::create($this->payload($request, true));

        return redirect()->route('bioskops.index')
            ->with('toast_success', 'Bioskop berhasil disimpan.');
    }

    public function edit(Bioskop $bioskop)
    {
        return view('bioskop.edit', [
            'title'   => 'Edit Bioskop Gratis',
            'bioskop' => $bioskop,
        ]);
    }

    public function update(Request $request, Bioskop $bioskop)
    {
        $bioskop->update($this->payload($request, false));

        return redirect()->route('bioskops.index')
            ->with('toast_success', 'Bioskop berhasil diperbarui.');
    }

    public function destroy(Bioskop $bioskop)
    {
        $bioskop->delete();

        return redirect()->route('bioskops.index')
            ->with('toast_success', 'Bioskop berhasil dihapus.');
    }

    protected function payload(Request $request, bool $isCreate): array
    {
        $statuses = Bioskop::STATUS_AVAILABLE . ',' . Bioskop::STATUS_FULL;

        $validated = $request->validate([
            'name'                               => 'required|string|max:255',
            'venue'                              => 'nullable|string|max:255',
            'address'                            => 'nullable|string|max:255',
            'sort_order'                         => 'nullable|integer|min:0',
            'is_active'                          => 'nullable|boolean',
            'schedules'                          => 'required|array|min:1',
            'schedules.*.date'                   => 'required|date|distinct',
            'schedules.*.sessions'               => 'required|array|min:1',
            'schedules.*.sessions.*.label'       => 'required|string|max:30',
            'schedules.*.sessions.*.time'        => 'required|date_format:H:i',
            'schedules.*.sessions.*.status'      => 'required|in:' . $statuses,
        ], [
            'schedules.required'                      => 'Minimal isi satu tanggal tayang.',
            'schedules.*.date.required'               => 'Tanggal tayang wajib diisi.',
            'schedules.*.date.distinct'               => 'Ada tanggal tayang yang dobel.',
            'schedules.*.sessions.required'           => 'Setiap tanggal minimal punya satu sesi.',
            'schedules.*.sessions.*.label.required'   => 'Nama sesi wajib diisi.',
            'schedules.*.sessions.*.time.required'    => 'Jam sesi wajib diisi.',
            'schedules.*.sessions.*.time.date_format' => 'Format jam sesi tidak valid.',
        ]);

        // Rapikan: urutkan tanggal & sesi (berdasarkan jam), buang index acak dari form.
        $schedules = collect($validated['schedules'])
            ->map(function ($schedule) {
                return [
                    'date'     => date('Y-m-d', strtotime($schedule['date'])),
                    'sessions' => collect($schedule['sessions'])->sortBy('time')->values()->all(),
                ];
            })
            ->sortBy('date')
            ->values()
            ->all();

        return [
            'name'       => $validated['name'],
            'venue'      => $validated['venue'] ?? null,
            'address'    => $validated['address'] ?? null,
            'schedules'  => $schedules,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active'  => $isCreate
                ? $request->boolean('is_active', true)
                : $request->boolean('is_active'),
        ];
    }
}