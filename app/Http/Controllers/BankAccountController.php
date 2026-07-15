<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    public function index()
    {
        return view('bank-account.index', [
            'title' => 'Rekening Pembayaran',
            'bankAccounts' => BankAccount::latest()->get(),
        ]);
    }

    public function create()
    {
        return view('bank-account.create', [
            'title' => 'Tambah Rekening',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);

        BankAccount::create(array_merge($validated, [
            'is_active' => $request->boolean('is_active', true),
        ]));

        return redirect()->route('bank-accounts.index')
            ->with('toast_success', 'Rekening berhasil disimpan.');
    }

    public function edit(BankAccount $bankAccount)
    {
        return view('bank-account.edit', [
            'title' => 'Edit Rekening',
            'bankAccount' => $bankAccount,
        ]);
    }

    public function update(Request $request, BankAccount $bankAccount)
    {
        $validated = $this->validateRequest($request);

        $bankAccount->update(array_merge($validated, [
            'is_active' => $request->boolean('is_active'),
        ]));

        return redirect()->route('bank-accounts.index')
            ->with('toast_success', 'Rekening berhasil diperbarui.');
    }

    public function destroy(BankAccount $bankAccount)
    {
        $bankAccount->delete();

        return back()->with('toast_success', 'Rekening berhasil dihapus.');
    }

    protected function validateRequest(Request $request)
    {
        return $request->validate([
            'rek_name' => 'required|string|max:255',
            'rek_bank_name' => 'required|string|max:255',
            'rek_bank_no' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);
    }
}
