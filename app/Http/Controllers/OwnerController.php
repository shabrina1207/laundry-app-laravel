<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\Member;
use App\Models\Outlet;
use App\Models\Paket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OwnerController extends Controller {

    public function dashboard() {

        $user = auth()->user();


        $stats = [

            'totalRevenue' => $this->calculateTotalRevenue(),
            'monthlyRevenue' => $this->calculateMonthlyRevenue(),
            'totalMembers' => Member::count(),
            'totalTransactions' => Transaksi::count(),
            'todayTransactions' => Transaksi::whereDate('tgl', today())->count(),
            'completedTransactions' => Transaksi::where('status', 'diambil')->count(),
            'pendingPayments' => Transaksi::where('dibayar', 'belum_dibayar')->count(),
            'pendingTransactions' => Transaksi::where('status', 'baru')->count(),
            'processingTransactions' => Transaksi::where('status', 'proses')->count(),

        ];


        $recentTransactions = Transaksi::with(['member', 'outlet', 'detailTransaksi.paket'])
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();


        $weeklyRevenue = $this->getWeeklyRevenue();
        $topOutlets = $this->getTopOutlets();


        return view('dashboard.owner', compact('stats', 'recentTransactions', 'weeklyRevenue', 'topOutlets', 'user'));

    }

    private function calculateTotalRevenue() {

        try {

            return Transaksi::where('dibayar', 'dibayar')
                ->with('detailTransaksi.paket')
                ->get()
                
                ->sum(function($transaction) {
                    $subtotal = $transaction->detailTransaksi->sum('subtotal');
                    return $subtotal
                        + ($transaction->biaya_tambahan ?? 0)
                        - ($transaction->diskon ?? 0)
                        + ($transaction->pajak ?? 0);

                });

        } catch (\Exception $e) {

            \Log::error('Error calculating total revenue: ' . $e->getMessage());

            return 0;

        }

    }

    private function calculateMonthlyRevenue() {

        try {

            return Transaksi::where('dibayar', 'dibayar')
                ->whereMonth('tgl', now()->month)
                ->whereYear('tgl', now()->year)
                ->with('detailTransaksi.paket')
                ->get()

                ->sum(function($transaction) {

                    $subtotal = $transaction->detailTransaksi->sum('subtotal');
                    return $subtotal
                        + ($transaction->biaya_tambahan ?? 0)
                        - ($transaction->diskon ?? 0)
                        + ($transaction->pajak ?? 0);

                });

        } catch (\Exception $e) {

            \Log::error('Error calculating monthly revenue: ' . $e->getMessage());

            return 0;

        }

    }

    private function getWeeklyRevenue() {

        try {

            $revenueData = Transaksi::where('dibayar', 'dibayar')
                ->whereBetween('tgl', [now()->subDays(7), now()])
                ->with('detailTransaksi.paket')
                ->get()

                ->groupBy(function($transaction) {

                    return $transaction->tgl->format('d-m-Y');

                })

                ->map(function($transactions) {

                    return $transactions->sum(function($transaction) {

                        $subtotal = $transaction->detailTransaksi->sum('subtotal');
                        return $subtotal
                            + ($transaction->biaya_tambahan ?? 0)
                            - ($transaction->diskon ?? 0)
                            + ($transaction->pajak ?? 0);

                    });

                });


            $result = [];

            for ($i = 6; $i >= 0; $i--) {

                $date = now()->subDays($i)->format('d-m-Y');

                $result[] = [

                    'date' => $date,
                    'revenue' => $revenueData[$date] ?? 0

                ];

            }

            return $result;

        } catch (\Exception $e) {

            \Log::error('Error getting weekly revenue: ' . $e->getMessage());

            return [];

        }

    }

    private function getTopOutlets() {

        try {

            return Outlet::withCount(['transaksi as total_transactions'])
                ->get()

                ->map(function($outlet) {
                    $revenue = Transaksi::where('id_outlet', $outlet->id)
                        ->where('dibayar', 'dibayar')
                        ->with('detailTransaksi.paket')
                        ->get()

                        ->sum(function($transaction) {

                            $subtotal = $transaction->detailTransaksi->sum('subtotal');
                            return $subtotal
                                + ($transaction->biaya_tambahan ?? 0)
                                - ($transaction->diskon ?? 0)
                                + ($transaction->pajak ?? 0);

                        });

                    $outlet->total_revenue = $revenue;

                    return $outlet;

                })

                ->sortByDesc('total_revenue')
                ->take(5);

        } catch (\Exception $e) {

            \Log::error('Error getting top outlets: ' . $e->getMessage());
            return collect([]);

        }

    }

}
