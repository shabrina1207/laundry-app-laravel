<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Models\Tblog;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OutletController extends Controller {

    public function index() {

        try {

            $outlets = Outlet::withCount('users')->get();


            Tblog::create([

                'id_user' => auth()->id(),
                'aktivitas' => 'Melihat daftar outlet',
                'data_terkait' => json_encode(['action' => 'view_outlets_list'])

            ]);

            return view('outlets.index', compact('outlets'));

        } catch (\Exception $e) {

            Log::error('Error fetching outlets: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')
                ->with('error', 'Gagal memuat data outlet: ' . $e->getMessage());

        }

    }


    public function create() {

        return view('outlets.create');

    }


    public function store(Request $request) {

        $validated = $request->validate([

            'nama' => 'required|string|max:100|unique:outlet,nama',
            'alamat' => 'required|string|min:10',
            'tlp' => 'required|string|max:15|unique:outlet,tlp'

        ], [

            'nama.required' => 'Nama outlet wajib diisi',
            'nama.unique' => 'Nama outlet sudah digunakan',
            'alamat.required' => 'Alamat outlet wajib diisi',
            'alamat.min' => 'Alamat terlalu pendek (minimal 10 karakter)',
            'tlp.required' => 'Nomor telepon wajib diisi',
            'tlp.unique' => 'Nomor telepon sudah digunakan'

        ]);

        try {

            $outlet = Outlet::create($validated);


            Tblog::create([

                'id_user' => auth()->id(),
                'aktivitas' => "Menambah outlet baru: {$outlet->nama}",

                'data_terkait' => json_encode([

                    'outlet_id' => $outlet->id,
                    'outlet_name' => $outlet->nama,
                    'alamat' => $outlet->alamat,
                    'telepon' => $outlet->tlp

                ])

            ]);

            return redirect()->route('admin.outlets.index')
                ->with('success', 'Outlet berhasil ditambahkan!');

        } catch (\Exception $e) {

            Log::error('Error creating outlet: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menambahkan outlet: ' . $e->getMessage())
                ->withInput();

        }

    }


    public function show(Outlet $outlet) {

        try {

            $outlet->load(['users' => function($query) {

                $query->take(10);

            }]);


            $transaksiCount = Transaksi::where('id_outlet', $outlet->id)->count();


            Tblog::create([

                'id_user' => auth()->id(),
                'aktivitas' => "Melihat detail outlet: {$outlet->nama}",

                'data_terkait' => json_encode([

                    'outlet_id' => $outlet->id,
                    'outlet_name' => $outlet->nama

                ])

            ]);

            return view('outlets.show', compact('outlet', 'transaksiCount'));

        } catch (\Exception $e) {

            Log::error('Error showing outlet: ' . $e->getMessage());
            return redirect()->route('admin.outlets.index')
                ->with('error', 'Gagal memuat detail outlet: ' . $e->getMessage());

        }

    }


    public function edit(Outlet $outlet) {

        return view('outlets.edit', compact('outlet'));

    }


    public function update(Request $request, Outlet $outlet) {

        $validated = $request->validate([

            'nama' => 'required|string|max:100|unique:outlet,nama,' . $outlet->id,
            'alamat' => 'required|string|min:10',
            'tlp' => 'required|string|max:15|unique:outlet,tlp,' . $outlet->id

        ], [

            'nama.required' => 'Nama outlet wajib diisi',
            'nama.unique' => 'Nama outlet sudah digunakan',
            'alamat.required' => 'Alamat outlet wajib diisi',
            'alamat.min' => 'Alamat terlalu pendek (minimal 10 karakter)',
            'tlp.required' => 'Nomor telepon wajib diisi',
            'tlp.unique' => 'Nomor telepon sudah digunakan'

        ]);

        try {

            $oldData = [

                'nama' => $outlet->nama,
                'alamat' => $outlet->alamat,
                'tlp' => $outlet->tlp

            ];

            $outlet->update($validated);


            Tblog::create([

                'id_user' => auth()->id(),
                'aktivitas' => "Memperbarui outlet: {$outlet->nama}",

                'data_terkait' => json_encode([

                    'outlet_id' => $outlet->id,
                    'outlet_name' => $outlet->nama,
                    'changes' => [
                        'nama' => ['old' => $oldData['nama'], 'new' => $validated['nama']],
                        'alamat' => ['old' => $oldData['alamat'], 'new' => $validated['alamat']],
                        'tlp' => ['old' => $oldData['tlp'], 'new' => $validated['tlp']]

                    ]

                ])

            ]);

            return redirect()->route('admin.outlets.index')
                ->with('success', 'Outlet berhasil diperbarui!');

        } catch (\Exception $e) {

            Log::error('Error updating outlet: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal memperbarui outlet: ' . $e->getMessage())
                ->withInput();

        }

    }


    public function destroy(Outlet $outlet) {

        try {

            $userCount = $outlet->users()->count();

            if ($userCount > 0) {

                return redirect()->back()
                    ->with('error', "Tidak dapat menghapus outlet karena masih memiliki {$userCount} user terkait.");

            }


            $transaksiCount = Transaksi::where('id_outlet', $outlet->id)->count();

            if ($transaksiCount > 0) {

                return redirect()->back()
                    ->with('error', "Tidak dapat menghapus outlet karena masih memiliki {$transaksiCount} transaksi terkait.");

            }

            $outletName = $outlet->nama;
            $outletId = $outlet->id;

            $outlet->delete();


            Tblog::create([

                'id_user' => auth()->id(),
                'aktivitas' => "Menghapus outlet: {$outletName}",
                'data_terkait' => json_encode([
                    'deleted_outlet_id' => $outletId,
                    'deleted_outlet_name' => $outletName
                ])

            ]);

            return redirect()->route('admin.outlets.index')
                ->with('success', 'Outlet berhasil dihapus!');

        } catch (\Exception $e) {

            Log::error('Error deleting outlet: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menghapus outlet: ' . $e->getMessage());

        }

    }

}
