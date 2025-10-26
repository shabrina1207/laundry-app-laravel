<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\Member;
use App\Models\Paket;
use App\Models\Outlet;
use App\Models\TbLog;
use App\Models\User;
use App\Http\Requests\StoreTransaksiRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Helpers\FormatHelper;

class TransaksiController extends Controller {

    public function __construct() {
        
        $this->middleware('auth');

    }

    public function index() {

        $user = Auth::user();

        if ($user->isAdmin || $user->isKasir) {

            $transaksi = Transaksi::with(['member', 'user', 'outlet'])
                ->orderBy('tgl', 'desc')
                ->get();

        } else {

            $transaksi = Transaksi::with(['member', 'user', 'outlet'])
                ->where('id_outlet', $user->id_outlet)
                ->orderBy('tgl', 'desc')
                ->get();

        }

        return view('transaksi.index', compact('transaksi'));

    }

    public function create() {

        $user = Auth::user();

        if ($user->isAdmin || $user->isKasir) {

            $members = Member::all();
            $outlets = Outlet::all();
            $pakets = Paket::with('outlet')->get();

        } else {

            $members = Member::all();
            $outlets = Outlet::where('id', $user->id_outlet)->get();
            $pakets = Paket::where('id_outlet', $user->id_outlet)->get();

        }

        return view('transaksi.create', compact('members', 'pakets', 'outlets'));

    }

    public function store(Request $request) {

        $request->merge([

            'biaya_tambahan' => $request->biaya_tambahan ?: 0,
            'pajak' => $request->pajak ?: 0,
            'diskon' => $request->diskon ?: 0

        ]);

        $rules = [

            'id_outlet' => 'required|exists:outlet,id',
            'tipe_pelanggan' => 'required|in:member,biasa',
            'durasi' => 'required|integer|min:1|max:30',
            'biaya_tambahan' => 'nullable|numeric|min:0|max:1000000',
            'diskon' => 'nullable|numeric|min:0|max:100',
            'pajak' => 'nullable|numeric|min:0|max:1000000',
            'id_paket' => 'required|array|min:1',
            'id_paket.*' => 'required|exists:paket,id',
            'qty' => 'required|array|min:1',
            'qty.*' => 'required|numeric|min:0.1|max:100',
            'berat' => 'nullable|array',
            'berat.*' => 'nullable|numeric|min:0.1|max:100',
            'keterangan' => 'nullable|array',
            'keterangan.*' => 'nullable|string|max:255'

        ];

        if ($request->tipe_pelanggan === 'member') {

            $rules['id_member'] = 'required|exists:member,id';
            $rules['nama_pelanggan'] = 'nullable|string|max:100';
            $rules['tlp_pelanggan'] = 'nullable|string|max:15|regex:/^[0-9]+$/';

        } else {

            $rules['id_member'] = 'nullable';
            $rules['nama_pelanggan'] = 'required|string|max:100';
            $rules['tlp_pelanggan'] = 'nullable|string|max:15|regex:/^[0-9]+$/';

        }

        $validator = Validator::make($request->all(), $rules, [

            'id_outlet.required' => 'Outlet wajib dipilih',
            'id_outlet.exists' => 'Outlet yang dipilih tidak valid',
            'tipe_pelanggan.required' => 'Tipe pelanggan wajib dipilih',
            'tipe_pelanggan.in' => 'Tipe pelanggan harus member atau biasa',
            'id_member.required' => 'Member wajib dipilih untuk tipe pelanggan member',
            'id_member.exists' => 'Member yang dipilih tidak valid',
            'nama_pelanggan.required' => 'Nama pelanggan wajib diisi untuk tipe pelanggan biasa',
            'nama_pelanggan.string' => 'Nama pelanggan harus berupa teks',
            'nama_pelanggan.max' => 'Nama pelanggan maksimal 100 karakter',
            'tlp_pelanggan.string' => 'Nomor telepon harus berupa teks',
            'tlp_pelanggan.max' => 'Nomor telepon maksimal 15 karakter',
            'tlp_pelanggan.regex' => 'Nomor telepon hanya boleh mengandung angka.',
            'durasi.required' => 'Durasi pengerjaan wajib diisi',
            'durasi.integer' => 'Durasi harus berupa angka',
            'durasi.min' => 'Durasi minimal 1 hari',
            'durasi.max' => 'Durasi maksimal 30 hari',
            'biaya_tambahan.numeric' => 'Biaya tambahan harus berupa angka',
            'biaya_tambahan.min' => 'Biaya tambahan minimal 0',
            'biaya_tambahan.max' => 'Biaya tambahan maksimal 1.000.000',
            'diskon.numeric' => 'Diskon harus berupa angka',
            'diskon.min' => 'Diskon minimal 0%',
            'diskon.max' => 'Diskon tidak boleh lebih dari 100%.',
            'pajak.numeric' => 'Pajak harus berupa angka',
            'pajak.min' => 'Pajak minimal 0',
            'pajak.max' => 'Pajak maksimal 1.000.000',
            'id_paket.required' => 'Minimal satu paket harus dipilih',
            'id_paket.array' => 'Format paket tidak valid',
            'id_paket.min' => 'Minimal satu paket harus dipilih',
            'id_paket.*.required' => 'Setiap paket wajib dipilih',
            'id_paket.*.exists' => 'Paket yang dipilih tidak valid',
            'qty.required' => 'Quantity wajib diisi',
            'qty.array' => 'Format quantity tidak valid',
            'qty.min' => 'Minimal satu quantity harus diisi',
            'qty.*.required' => 'Setiap quantity wajib diisi',
            'qty.*.numeric' => 'Quantity harus berupa angka',
            'qty.*.min' => 'Quantity minimal 0.1',
            'qty.*.max' => 'Quantity maksimal 100',
            'berat.*.numeric' => 'Berat harus berupa angka',
            'berat.*.min' => 'Berat minimal 0.1 kg',
            'berat.*.max' => 'Berat maksimal 100 kg',
            'keterangan.*.string' => 'Keterangan harus berupa teks',
            'keterangan.*.max' => 'Keterangan maksimal 255 karakter'

        ]);

        if ($validator->fails()) {

            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Terjadi kesalahan validasi. Silakan periksa kembali data yang dimasukkan.');

        }

        try {

            DB::beginTransaction();

            $tanggal = now()->format('dmY');
            $lastTransaksi = Transaksi::whereDate('tgl', today())->count();
            $kodeInvoice = 'INV-' . $tanggal . '-' . str_pad($lastTransaksi + 1, 3, '0', STR_PAD_LEFT);

            $totalBiayaPaket = 0;
            $detailTransaksi = [];

            foreach ($request->id_paket as $index => $idPaket) {

                $paket = Paket::find($idPaket);

                if (!$paket) {

                    throw new \Exception("Paket dengan ID {$idPaket} tidak ditemukan");

                }

                if ($paket->jenis === 'kiloan') {

                    $qty = $request->berat[$index] ?? $request->qty[$index];
                    $berat = $request->berat[$index] ?? $request->qty[$index];

                } else {

                    $qty = $request->qty[$index];
                    $berat = null;

                }

                $subtotal = $paket->harga * $qty;
                $totalBiayaPaket += $subtotal;

                $detailTransaksi[] = [

                    'id_paket' => $idPaket,
                    'qty' => $qty,
                    'berat' => $berat,
                    'keterangan' => $request->keterangan[$index] ?? null,

                ];

            }

            $biayaTambahan = $request->biaya_tambahan ?? 0;
            $diskon = $request->diskon ? ($request->diskon / 100) : 0;
            $pajak = $request->pajak ?? 0;

            $totalAkhir = $totalBiayaPaket + $biayaTambahan - ($totalBiayaPaket * $diskon) + $pajak;

            $transaksiData = [

                'id_outlet' => $request->id_outlet,
                'kode_invoice' => $kodeInvoice,
                'tipe_pelanggan' => $request->tipe_pelanggan,
                'tgl' => now(),
                'batas_waktu' => Carbon::now()->addDays((int) ($request->durasi ?? 2)),
                'biaya_tambahan' => $biayaTambahan,
                'diskon' => $diskon,
                'pajak' => $pajak,
                'status' => 'baru',
                'dibayar' => 'belum_dibayar',
                'id_user' => Auth::id(),

            ];

            if ($request->tipe_pelanggan === 'member') {

                $transaksiData['id_member'] = $request->id_member;
                $transaksiData['nama_pelanggan'] = null;
                $transaksiData['tlp_pelanggan'] = null;

            } else {

                $transaksiData['id_member'] = null;
                $transaksiData['nama_pelanggan'] = $request->nama_pelanggan;
                $transaksiData['tlp_pelanggan'] = $request->tlp_pelanggan;

            }

            $transaksi = Transaksi::create($transaksiData);

            foreach ($detailTransaksi as $detail) {

                DetailTransaksi::create([

                    'id_transaksi' => $transaksi->id,
                    'id_paket' => $detail['id_paket'],
                    'qty' => $detail['qty'],
                    'berat' => $detail['berat'],
                    'keterangan' => $detail['keterangan'],

                ]);

            }

            TbLog::create([

                'id_user' => Auth::id(),
                'aktivitas' => 'Membuat transaksi baru',
                'tanggal' => now(),

                'data_terkait' => json_encode([

                    'kode_invoice' => $kodeInvoice,
                    'id_transaksi' => $transaksi->id,
                    'tipe_pelanggan' => $request->tipe_pelanggan,
                    'total' => FormatHelper::rupiah($totalAkhir),
                    'diskon' => FormatHelper::persen($diskon)

                ])

            ]);

            DB::commit();

            return redirect()->route('transaksi.show', $transaksi->id)
                ->with('success', 'Transaksi berhasil dibuat! Kode Invoice: ' . $kodeInvoice);

        } catch (\Exception $e) {

            DB::rollBack();

            \Log::error('Error saat menyimpan transaksi: ' . $e->getMessage());
            \Log::error('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            \Log::error('Request Data: ', $request->all());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();

        }

    }

    public function show($id) {

        $transaksi = Transaksi::with([

            'member',
            'user',
            'outlet',
            'detailTransaksi.paket'

        ])->findOrFail($id);

        return view('transaksi.show', compact('transaksi'));

    }

    public function updateStatus(Request $request, $id) {

        $request->validate([

            'status' => 'required|in:baru,proses,selesai,diambil',
            'dibayar' => 'required|in:dibayar,belum_dibayar'

        ]);

        $transaksi = Transaksi::findOrFail($id);

        $updateData = [

            'status' => $request->status,
            'dibayar' => $request->dibayar,

        ];

        if ($request->dibayar == 'dibayar' && $transaksi->dibayar != 'dibayar') {

            $updateData['tgl_bayar'] = now();

        } elseif ($request->dibayar == 'belum_dibayar') {

            $updateData['tgl_bayar'] = null;

        }

        $transaksi->update($updateData);

        TbLog::create([

            'id_user' => Auth::id(),
            'aktivitas' => 'Update status transaksi',
            'tanggal' => now(),
            'data_terkait' => json_encode([

                'kode_invoice' => $transaksi->kode_invoice,
                'status_sebelum' => $transaksi->status,
                'status_sesudah' => $request->status,
                'pembayaran_sebelum' => $transaksi->dibayar,
                'pembayaran_sesudah' => $request->dibayar

            ])

        ]);

        return redirect()->back()->with('success', 'Status transaksi berhasil diupdate!');

    }

    public function updateStatusQuick(Request $request, $id) {

        $request->validate([

            'field' => 'required|in:status,dibayar',
            'value' => 'required'

        ]);

        $transaksi = Transaksi::findOrFail($id);


        if ($request->field === 'status') {

            $request->validate(['value' => 'in:baru,proses,selesai,diambil']);

        } else {

            $request->validate(['value' => 'in:dibayar,belum_dibayar']);

        }

        $updateData = [

            $request->field => $request->value

        ];


        if ($request->field === 'dibayar' && $request->value == 'dibayar' && $transaksi->dibayar != 'dibayar') {

            $updateData['tgl_bayar'] = now();

        } elseif ($request->field === 'dibayar' && $request->value == 'belum_dibayar') {

            $updateData['tgl_bayar'] = null;

        }

        $oldValue = $transaksi->{$request->field};
        $transaksi->update($updateData);


        TbLog::create([

            'id_user' => Auth::id(),
            'aktivitas' => 'Update status transaksi dari daftar',
            'tanggal' => now(),
            'data_terkait' => json_encode([

                'kode_invoice' => $transaksi->kode_invoice,
                'field' => $request->field,
                'nilai_sebelum' => $oldValue,
                'nilai_sesudah' => $request->value

            ])

        ]);

        return response()->json([

            'success' => true,
            'message' => 'Status berhasil diupdate!',
            'new_value' => $request->value,
            'field' => $request->field,
            'new_value_formatted' => $request->field === 'status'
                ? $transaksi->status_formatted
                : $transaksi->dibayar_formatted

        ]);

    }

    public function printInvoice($id) {

        $transaksi = Transaksi::with([

            'member',
            'user',
            'outlet',
            'detailTransaksi.paket'

        ])->findOrFail($id);

        return view('transaksi.invoice', compact('transaksi'));

    }

    public function getPaketByOutlet($outletId) {

        $pakets = Paket::where('id_outlet', $outletId)->get();
        return response()->json($pakets);

    }

}
