<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Transaksi;
use App\Models\Paket;
use App\Models\Outlet;
use App\Models\TbLog;
use Illuminate\Http\Request;

class KasirController extends Controller {

    public function dashboard() {

        try {

            $user = auth()->user();

            $data = [

                'totalMembers' => Member::count(),
                'todayTransactions' => Transaksi::whereDate('tgl', today())->count(),
                'totalTransactions' => Transaksi::count(),
                'totalPakets' => Paket::count(),

                'myOutletTransactions' => $user->id_outlet ?
                    Transaksi::where('id_outlet', $user->id_outlet)->count() : 0,
                'myOutletTodayTransactions' => $user->id_outlet ?
                    Transaksi::where('id_outlet', $user->id_outlet)
                        ->whereDate('tgl', today())->count() : 0,

                'recentMembers' => Member::orderBy('id', 'desc')->take(5)->get(),

                'recentTransactions' => Transaksi::with(['member', 'detailTransaksi'])

                    ->when($user->id_outlet, function($query) use ($user) {

                        return $query->where('id_outlet', $user->id_outlet);

                    })

                    ->latest('tgl')
                    ->take(5)
                    ->get(),

                'pendingTransactions' => Transaksi::where('status', 'baru')

                    ->when($user->id_outlet, function($query) use ($user) {

                        return $query->where('id_outlet', $user->id_outlet);

                    })

                    ->count(),

                'processingTransactions' => Transaksi::where('status', 'proses')

                    ->when($user->id_outlet, function($query) use ($user) {

                        return $query->where('id_outlet', $user->id_outlet);
                    })

                    ->count(),

                'completedTransactions' => Transaksi::where('status', 'selesai')

                    ->when($user->id_outlet, function($query) use ($user) {

                        return $query->where('id_outlet', $user->id_outlet);

                    })

                    ->count(),

            ];

            return view('dashboard.kasir', $data);

        } catch (\Exception $e) {

            \Log::error('Kasir dashboard error: ' . $e->getMessage());

            return view('dashboard.kasir', [

                'totalMembers' => 0,
                'todayTransactions' => 0,
                'totalTransactions' => 0,
                'totalPakets' => 0,
                'myOutletTransactions' => 0,
                'myOutletTodayTransactions' => 0,
                'recentMembers' => collect(),
                'recentTransactions' => collect(),
                'pendingTransactions' => 0,
                'processingTransactions' => 0,
                'completedTransactions' => 0,

            ]);

        }

    }

    public function laporan() {

        try {

            $user = auth()->user();
            $startDate = request('start_date', now()->startOfMonth()->format('d-m-Y'));
            $endDate = request('end_date', now()->format('d-m-Y'));

            $outlets = Outlet::all();

            $transactions = Transaksi::with(['member', 'detailTransaksi.paket'])

                ->when($user->id_outlet, function($query) use ($user) {

                    return $query->where('id_outlet', $user->id_outlet);

                })

                ->whereBetween('tgl', [$startDate, $endDate])
                ->latest('tgl')
                ->get();

            $totalPendapatan = $transactions->where('dibayar', 'dibayar')

                ->sum(function($transaksi) {

                    $totalPaket = $transaksi->detailTransaksi->sum('subtotal');
                    return $totalPaket + ($transaksi->biaya_tambahan ?? 0) - ($transaksi->diskon ?? 0) + ($transaksi->pajak ?? 0);

                });

            $data = [

                'transactions' => $transactions,
                'totalPendapatan' => $totalPendapatan,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'userRole' => 'kasir',
                'reportTitle' => 'Laporan Kasir - ' . $user->name,
                'filterByOutlet' => $user->id_outlet ? true : false,
                'outlets' => $outlets,

            ];

            return view('reports.index', $data);

        } catch (\Exception $e) {

            \Log::error('Kasir laporan error: ' . $e->getMessage());
            return redirect()->route('kasir.dashboard')
                ->with('error', 'Gagal memuat laporan: ' . $e->getMessage());

        }

    }

    public function quickReport() {

        try {

            $user = auth()->user();

            $outlets = Outlet::all();

            $startDate = today()->format('d-m-Y');
            $endDate = today()->format('d-m-Y');

            $transactions = Transaksi::with(['member', 'detailTransaksi.paket'])

                ->when($user->id_outlet, function($query) use ($user) {

                    return $query->where('id_outlet', $user->id_outlet);
                })

                ->whereDate('tgl', today())
                ->latest('tgl')
                ->get();

            $totalPendapatan = $transactions->where('dibayar', 'dibayar')

                ->sum(function($transaksi) {

                    $totalPaket = $transaksi->detailTransaksi->sum('subtotal');
                    return $totalPaket + ($transaksi->biaya_tambahan ?? 0) - ($transaksi->diskon ?? 0) + ($transaksi->pajak ?? 0);

                });

            $data = [

                'transactions' => $transactions,
                'totalPendapatan' => $totalPendapatan,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'userRole' => 'kasir',
                'reportTitle' => 'Laporan Cepat - Hari Ini',
                'isQuickReport' => true,
                'outlets' => $outlets,

            ];

            return view('reports.index', $data);

        } catch (\Exception $e) {

            \Log::error('Kasir quick report error: ' . $e->getMessage());
            return redirect()->route('kasir.dashboard')
                ->with('error', 'Gagal memuat laporan cepat: ' . $e->getMessage());

        }

    }

}
