<?php

namespace App\Http\Controllers;

use App\Models\Paket;
use App\Models\Outlet;
use App\Models\Tblog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Helpers\FormatHelper;

class PaketController extends Controller {
    
    public function index() {

        $pakets = Paket::with('outlet')->latest()->get();
        return view('paket.index', compact('pakets'));

    }

    public function create() {

        $outlets = Outlet::all();
        $jenis = ['kiloan', 'selimut', 'bed_cover', 'kaos', 'lain'];
        return view('paket.create', compact('outlets', 'jenis'));

    }

    public function store(Request $request) {

        $request->validate([

            'id_outlet' => 'required|exists:outlet,id',
            'jenis' => 'required|in:kiloan,selimut,bed_cover,kaos,lain',
            'nama_paket' => 'required|string|max:100',
            'harga' => 'required|integer|min:1000|max:10000000'
        ], [

            'id_outlet.required' => 'Outlet wajib dipilih',
            'id_outlet.exists' => 'Outlet yang dipilih tidak valid',
            'jenis.required' => 'Jenis paket wajib dipilih',
            'jenis.in' => 'Jenis paket tidak valid',
            'nama_paket.required' => 'Nama paket wajib diisi',
            'nama_paket.max' => 'Nama paket maksimal 100 karakter',
            'harga.required' => 'Harga paket wajib diisi',
            'harga.integer' => 'Harga harus berupa angka',
            'harga.min' => 'Harga minimal Rp 1.000',
            'harga.max' => 'Harga maksimal Rp 10.000.000'

        ]);


        $existingPaket = Paket::where('id_outlet', $request->id_outlet)
            ->where('nama_paket', $request->nama_paket)
            ->first();

        if ($existingPaket) {

            $outletName = Outlet::find($request->id_outlet)->nama;
            return redirect()->back()
                ->with('error', "Nama paket '{$request->nama_paket}' sudah digunakan di outlet {$outletName}!")
                ->withInput();

        }

        try {

            $paket = Paket::create($request->all());


            Tblog::create([

                'id_user' => Auth::id(),
                'aktivitas' => 'Menambah paket: ' . $paket->nama_paket,
                'tanggal' => now(),
                'data_terkait' => json_encode([
                    'id_paket' => $paket->id,
                    'nama_paket' => $paket->nama_paket,
                    'jenis' => $paket->jenis,
                    'harga' => FormatHelper::rupiah($paket->harga),
                    'outlet' => $paket->outlet->nama

                ])

            ]);

            return redirect()->route(auth()->user()->role . '.paket.index')
                ->with('success', 'Paket berhasil ditambahkan.');

        } catch (\Exception $e) {

            \Log::error('Error creating paket: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menambahkan paket: ' . $e->getMessage())
                ->withInput();
        }

    }

    public function show(Paket $paket) {

        $paket->load('outlet');
        return view('paket.show', compact('paket'));

    }

    public function edit(Paket $paket) {

        $outlets = Outlet::all();
        $jenis = ['kiloan', 'selimut', 'bed_cover', 'kaos', 'lain'];
        return view('paket.edit', compact('paket', 'outlets', 'jenis'));

    }

    public function update(Request $request, Paket $paket) {

        $request->validate([

            'id_outlet' => 'required|exists:outlet,id',
            'jenis' => 'required|in:kiloan,selimut,bed_cover,kaos,lain',
            'nama_paket' => 'required|string|max:100',
            'harga' => 'required|integer|min:1000|max:10000000'
        ], [

            'id_outlet.required' => 'Outlet wajib dipilih',
            'id_outlet.exists' => 'Outlet yang dipilih tidak valid',
            'jenis.required' => 'Jenis paket wajib dipilih',
            'jenis.in' => 'Jenis paket tidak valid',
            'nama_paket.required' => 'Nama paket wajib diisi',
            'nama_paket.max' => 'Nama paket maksimal 100 karakter',
            'harga.required' => 'Harga paket wajib diisi',
            'harga.integer' => 'Harga harus berupa angka',
            'harga.min' => 'Harga minimal Rp 1.000',
            'harga.max' => 'Harga maksimal Rp 10.000.000'

        ]);


        $existingPaket = Paket::where('id_outlet', $request->id_outlet)
            ->where('nama_paket', $request->nama_paket)
            ->where('id', '!=', $paket->id)
            ->first();

        if ($existingPaket) {

            $outletName = Outlet::find($request->id_outlet)->nama;
            return redirect()->back()
                ->with('error', "Nama paket '{$request->nama_paket}' sudah digunakan di outlet {$outletName}!")
                ->withInput();

        }

        try {

            $oldData = $paket->toArray();
            $paket->update($request->all());


            Tblog::create([

                'id_user' => Auth::id(),
                'aktivitas' => 'Mengubah paket: ' . $paket->nama_paket,
                'tanggal' => now(),
                'data_terkait' => json_encode([

                    'id_paket' => $paket->id,
                    'before' => $oldData,
                    'after' => $paket->toArray(),
                    'outlet' => $paket->outlet->nama,
                    'harga_old' => FormatHelper::rupiah($oldData['harga']),
                    'harga_new' => FormatHelper::rupiah($paket->harga)

                ])

            ]);

            return redirect()->route(auth()->user()->role . '.paket.index')
                ->with('success', 'Paket berhasil diperbarui.');

        } catch (\Exception $e) {

            \Log::error('Error updating paket: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal memperbarui paket: ' . $e->getMessage())
                ->withInput();

        }

    }

    public function destroy(Paket $paket) {

        try {

            if ($paket->detailTransaksi()->count() > 0) {

                return redirect()->back()
                    ->with('error', 'Tidak dapat menghapus paket karena sudah digunakan dalam transaksi!');

            }

            $namaPaket = $paket->nama_paket;
            $outletName = $paket->outlet->nama;


            Tblog::create([

                'id_user' => Auth::id(),
                'aktivitas' => 'Menghapus paket: ' . $namaPaket,
                'tanggal' => now(),
                'data_terkait' => json_encode([
                    'deleted_paket' => $namaPaket,
                    'jenis' => $paket->jenis,
                    'harga' => FormatHelper::rupiah($paket->harga),
                    'outlet' => $outletName

                ])

            ]);

            $paket->delete();

            return redirect()->route(auth()->user()->role . '.paket.index')
                ->with('success', 'Paket berhasil dihapus.');

        } catch (\Exception $e) {

            \Log::error('Error deleting paket: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menghapus paket: ' . $e->getMessage());

        }

    }


    public function checkDuplicate(Request $request) {

        try {

            $request->validate([

                'id_outlet' => 'required|exists:outlet,id',
                'nama_paket' => 'required|string|max:100',
                'paket_id' => 'nullable|exists:paket,id'

            ]);

            $query = Paket::where('id_outlet', $request->id_outlet)
                ->where('nama_paket', $request->nama_paket);

            if ($request->has('paket_id') && $request->paket_id) {

                $query->where('id', '!=', $request->paket_id);

            }

            $exists = $query->exists();
            $outletName = Outlet::find($request->id_outlet)->nama;

            return response()->json([

                'exists' => $exists,
                'message' => $exists ? "Nama paket '{$request->nama_paket}' sudah digunakan di outlet {$outletName}" : 'Nama paket tersedia'

            ]);

        } catch (\Exception $e) {

            return response()->json([

                'error' => 'Terjadi kesalahan saat validasi',
                'exists' => false

            ], 500);

        }

    }

}
