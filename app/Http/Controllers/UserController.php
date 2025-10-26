<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Outlet;
use App\Models\Tblog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller {

    public function index() {

        $users = User::with('outlet')->get();


        $this->logAktivitas('lihat_pengguna', 'Melihat daftar pengguna');

        return view('admin.users.index', compact('users'));

    }


    public function create() {

        $outlets = Outlet::all();
        $roles = ['admin', 'kasir', 'owner'];

        return view('admin.users.create', compact('outlets', 'roles'));

    }


    public function store(Request $request) {

        $validator = $this->validateUser($request);

        if ($validator->fails()) {

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();

        }

        $user = User::create([

            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'id_outlet' => $request->id_outlet,

        ]);


        $this->logAktivitas('tambah_pengguna', 'Menambah pengguna baru: ' . $user->name, $user->toArray());

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil ditambahkan!');

    }


    public function edit($id) {

        $user = User::findOrFail($id);
        $outlets = Outlet::all();
        $roles = ['admin', 'kasir', 'owner'];

        return view('admin.users.edit', compact('user', 'outlets', 'roles'));

    }


    public function update(Request $request, $id) {

        $user = User::findOrFail($id);

        $validator = $this->validateUser($request, $user->id);

        if ($validator->fails()) {

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();

        }

        $data = [

            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'role' => $request->role,
            'id_outlet' => $request->id_outlet,

        ];


        if ($request->filled('password')) {

            $data['password'] = Hash::make($request->password);

        }

        $user->update($data);


        $this->logAktivitas('ubah_pengguna', 'Mengubah data pengguna: ' . $user->name, $user->toArray());

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil diupdate!');

    }


    public function destroy($id) {

        $user = User::findOrFail($id);
        $userName = $user->name;


        $userData = $user->toArray();

        $user->delete();


        $this->logAktivitas('hapus_pengguna', 'Menghapus pengguna: ' . $userName, $userData);

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil dihapus!');

    }


    private function validateUser(Request $request, $userId = null) {

        $rules = [

            'name' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users')->ignore($userId)

            ],

            'email' => [

                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($userId)

            ],

            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'role' => 'required|in:admin,kasir,owner',
            'id_outlet' => 'nullable|exists:outlet,id',

        ];


        if (!$userId || $request->filled('password')) {

            $rules['password'] = 'required|min:8|confirmed';

        }

        return Validator::make($request->all(), $rules);

    }


    private function logAktivitas($aktivitas, $deskripsi, $dataTerkait = null) {

        Tblog::create([

            'id_user' => auth()->id(),
            'aktivitas' => $aktivitas,
            'tanggal' => now(),
            'data_terkait' => $dataTerkait,

        ]);

    }
    
}
