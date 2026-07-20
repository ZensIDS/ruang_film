<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Exports\PesertaExport;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('user.index', [
            'title' => 'User Admin',
            'users' => User::with('category')->where('role', '!=', 'peserta')->latest()->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('user.create', [
            'title' => 'Tambah User',
            'categories' => Category::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function createAuthor()
    {
        return view('user.create-author', [
            'title' => 'Tambah Data Peserta',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'role' => 'required',
            'no_hp' => $request->role === 'peserta' ? 'required|string|max:20' : 'nullable|string|max:20',
            'category_id' => [
                Rule::requiredIf(function () use ($request) {
                    return $request->role === 'juri';
                }),
                'nullable',
                'exists:categories,id',
            ],
        ]);

        $validatedData['password'] = Hash::make($validatedData['password']);
        $validatedData['category_id'] = $validatedData['role'] === 'juri'
            ? $request->input('category_id')
            : null;
        User::create($validatedData);

        if ($validatedData['role'] === 'peserta') {
            return redirect(route('users.index.author'))->with('toast_success', 'Berhasil Menyimpan Data!');
        }

        if (in_array($validatedData['role'], ['kurator', 'juri'])) {
            return redirect(route('users.index.kurator'))->with('toast_success', 'Berhasil Menyimpan Data!');
        }

        return redirect(route('users.index'))->with('toast_success', 'Berhasil Menyimpan Data!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        $user->load([
            'detail',
            'films.category'
        ]);

        return view('user.show', [
            'title' => 'Detail User',
            'users' => $user,
            'detail' => $user->detail,
            'films' => $user->films,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        return view('user.edit', [
            'title' => 'Edit User',
            'users' => $user,
            'categories' => Category::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'role' => 'required',
            'no_hp' => 'nullable|string|max:20',
            'category_id' => [
                Rule::requiredIf(function () use ($request, $user) {
                    return ($request->input('role') ?: $user->role) === 'juri';
                }),
                'nullable',
                'exists:categories,id',
            ],
        ]);

        $validatedData['category_id'] = $validatedData['role'] === 'juri'
            ? $request->input('category_id')
            : null;
        User::where('id', $user->id)->update($validatedData);
        if ($request->role == 'peserta') {
            return redirect(route('users.index.author'))->with('toast_success', 'Berhasil Menyimpan Data!');
        } elseif (in_array($request->role, ['kurator', 'juri'])) {
            return redirect(route('users.index.kurator'))->with('toast_success', 'Berhasil Menyimpan Data!');
        } else {
            return redirect(route('users.index'))->with('toast_success', 'Berhasil Menyimpan Data!');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        User::destroy($user->id);
        return back()->with('toast_success', 'Berhasil Menyimpan Data!');
    }

    public function changePass()
    {
        return view('user.change', [
            'title' => 'Ganti Password'
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|confirmed',
        ]);


        if (!Hash::check($request->old_password, auth()->user()->password)) {
            return back()->with('toast_error', 'Password Lama Tidak Sesuai');
        }


        User::whereId(auth()->user()->id)->update([
            'password' => Hash::make($request->new_password)
        ]);

        return redirect('/dashboard')->with('toast_success', 'Password Berhasil Dirubah');
    }

    public function indexAuth()
    {
        return view('user.indexAuth', [
            'title' => 'User Author',
            'users' => $this->getPesertaUsers(),
        ]);
    }

    public function indexKur()
    {
        return view('user.index', [
            'title' => 'User Kurator & Juri',
            'users' => User::with('category')->whereIn('role', ['kurator', 'juri'])->latest()->get(),
        ]);
    }

    public function exportPesertaExcel()
    {
        $users = $this->getPesertaUsers();

        $fileName = 'data-peserta_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new PesertaExport($users), $fileName);
    }

    /**
     * Dipakai bareng oleh indexAuth() dan exportPesertaExcel()
     * supaya data yang di-export selalu sama dengan yang tampil di halaman.
     */
    private function getPesertaUsers()
    {
        return User::with('detail')
            ->withCount('films') // hasil bisa diakses lewat $user->films_count
            ->where('role', 'peserta')
            ->latest()
            ->get();
    }
}
