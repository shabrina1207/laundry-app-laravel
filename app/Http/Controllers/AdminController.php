<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Models\Member;
use App\Models\User;
use App\Models\Transaksi;
use App\Models\Tblog;
use App\Models\Paket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminController extends Controller {

    public function dashboard() {

        try {

            \Log::info('=== ADMIN DASHBOARD DATA CHECK ===');
            \Log::info('Total Members: ' . Member::count());
            \Log::info('Total Transactions: ' . Transaksi::count());
            \Log::info('Total Outlets: ' . Outlet::count());
            \Log::info('Total Users: ' . User::count());
            \Log::info('Total Pakets: ' . Paket::count());

            $monthlyTransactions = Transaksi::whereMonth('tgl', now()->month)
                ->whereYear('tgl', now()->year)
                ->count();

            $monthlyRevenue = Transaksi::where('dibayar', 'dibayar')
                ->whereMonth('tgl', now()->month)
                ->whereYear('tgl', now()->year)
                ->get()

                ->sum(function($transaction) {

                    $subtotal = $transaction->detailTransaksi->sum(function($detail) {

                        return $detail->qty * $detail->paket->harga;
                    });

                    return $subtotal
                        + ($transaction->biaya_tambahan ?? 0)
                        - ($transaction->diskon ?? 0)
                        + ($transaction->pajak ?? 0);

                });

            $completedTransactions = Transaksi::where('status', 'diambil')
                ->where('dibayar', 'dibayar')
                ->count();

            $averageTransaction = $monthlyTransactions > 0 ? $monthlyRevenue / $monthlyTransactions : 0;

            $pendingTransactions = Transaksi::where('status', 'baru')->count();
            $processingTransactions = Transaksi::where('status', 'proses')->count();
            $totalTransactions = Transaksi::count();
            $todayTransactions = Transaksi::whereDate('tgl', today())->count();
            $totalMembers = Member::count();


            $recentMembers = Member::orderBy('id', 'desc')->take(5)->get();
            $recentTransactions = Transaksi::with(['member', 'outlet', 'detailTransaksi.paket'])
                ->orderBy('tgl', 'desc')
                ->take(5)
                ->get();

            foreach ($recentTransactions as $transaksi) {

                $batasWaktu = Carbon::parse($transaksi->tgl)->addDays($transaksi->batas_waktu);
                $transaksi->is_terlambat = now()->gt($batasWaktu) && $transaksi->status != 'diambil';

            }

            $data = [

                'totalOutlets' => Outlet::count(),
                'totalMembers' => $totalMembers,
                'totalUsers' => User::count(),
                'todayTransactions' => $todayTransactions,
                'totalPakets' => Paket::count(),
                'totalTransactions' => $totalTransactions,


                'recentOutlets' => Outlet::orderBy('id', 'desc')->take(5)->get(),
                'recentMembers' => $recentMembers,
                'recentPakets' => Paket::with('outlet')->orderBy('id', 'desc')->take(5)->get(),
                'recentLogs' => Tblog::with('user')
                    ->orderBy('tanggal', 'desc')
                    ->take(5)
                    ->get(),
                'recentUsers' => User::with('outlet')->orderBy('created_at', 'desc')->take(5)->get(),
                'recentTransactions' => $recentTransactions,

                'monthlyTransactions' => $monthlyTransactions,
                'monthlyRevenue' => $monthlyRevenue,
                'completedTransactions' => $completedTransactions,
                'averageTransaction' => $averageTransaction,

                'pendingTransactions' => $pendingTransactions,
                'processingTransactions' => $processingTransactions,

            ];


            \Log::info('=== DATA SENT TO VIEW ===');
            \Log::info('Recent Members: ' . $data['recentMembers']->count());
            \Log::info('Recent Transactions: ' . $data['recentTransactions']->count());
            \Log::info('Recent Outlets: ' . $data['recentOutlets']->count());
            \Log::info('Recent Users: ' . $data['recentUsers']->count());
            \Log::info('Recent Pakets: ' . $data['recentPakets']->count());
            \Log::info('Recent Logs: ' . $data['recentLogs']->count());

            return view('dashboard.admin', $data);

        } catch (\Exception $e) {

            \Log::error('Dashboard error: ' . $e->getMessage());
            \Log::error('Dashboard error trace: ' . $e->getTraceAsString());

            return view('dashboard.admin', [

                'totalOutlets' => 0,
                'totalMembers' => 0,
                'totalUsers' => 0,
                'todayTransactions' => 0,
                'totalPakets' => 0,
                'totalTransactions' => 0,
                'recentOutlets' => collect(),
                'recentMembers' => collect(),
                'recentPakets' => collect(),
                'recentLogs' => collect(),
                'recentUsers' => collect(),
                'recentTransactions' => collect(),
                'monthlyTransactions' => 0,
                'monthlyRevenue' => 0,
                'completedTransactions' => 0,
                'averageTransaction' => 0,
                'pendingTransactions' => 0,
                'processingTransactions' => 0,

            ]);

        }

    }

    public function showUsers() {

        try {

            $users = User::with('outlet')
                ->latest()
                ->get();

            return view('admin.users', compact('users'));

        } catch (\Exception $e) {

            \Log::error('Show users error: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')
                ->with('error', 'Gagal memuat data users: ' . $e->getMessage());

        }

    }

    public function updateUserRole(Request $request, $id) {

        $request->validate([

            'role' => 'required|in:admin,kasir,owner'

        ]);

        try {

            $user = User::findOrFail($id);

            if ($user->id === auth()->id()) {

                return redirect()->back()
                    ->with('error', 'Tidak dapat mengubah role sendiri.');

            }

            $oldRole = $user->role;
            $user->update(['role' => $request->role]);

            Tblog::create([

                'id_user' => auth()->id(),
                'aktivitas' => "Update user role: {$user->name} dari {$oldRole} menjadi {$request->role}",
                'tanggal' => now(),
                'data_terkait' => json_encode([

                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'old_role' => $oldRole,
                    'new_role' => $request->role

                ])

            ]);

            return redirect()->back()
                ->with('success', 'Role user berhasil diperbarui.');

        } catch (\Exception $e) {

            \Log::error('Update user role error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal memperbarui role: ' . $e->getMessage());

        }

    }

    public function deleteUser($id) {

        try {

            $user = User::findOrFail($id);

            if ($user->id === auth()->id()) {

                return redirect()->back()
                    ->with('error', 'Tidak dapat menghapus akun sendiri.');

            }

            $userName = $user->name;
            $user->delete();

            Tblog::create([

                'id_user' => auth()->id(),
                'aktivitas' => "Delete user: {$userName}",
                'tanggal' => now(),
                'data_terkait' => json_encode([
                    'deleted_user_id' => $id,
                    'deleted_user_name' => $userName

                ])

            ]);

            return redirect()->back()
                ->with('success', 'User berhasil dihapus.');

        } catch (\Exception $e) {

            \Log::error('Delete user error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menghapus user: ' . $e->getMessage());

        }

    }


    public function showLogs() {

        try {

            $logs = Tblog::with('user')
                ->orderBy('tanggal', 'desc')
                ->paginate(20);

            return view('admin.logs', compact('logs'));

        } catch (\Exception $e) {

            \Log::error('Show logs error: ' . $e->getMessage());

            return redirect()->route('admin.dashboard')
                ->with('error', 'Gagal memuat logs: ' . $e->getMessage());

        }

    }

    public function clearLogs() {

        try {

            $deletedCount = Tblog::count();
            Tblog::truncate();

            Tblog::create([

                'id_user' => auth()->id(),
                'aktivitas' => "Clear all system logs ({$deletedCount} records deleted)",
                'tanggal' => now(),
                'data_terkait' => json_encode([

                    'deleted_records' => $deletedCount,
                    'cleared_by' => auth()->user()->name

                ])

            ]);

            return redirect()->route('admin.logs')
                ->with('success', "Berhasil menghapus {$deletedCount} records log.");

        } catch (\Exception $e) {

            \Log::error('Clear logs error: ' . $e->getMessage());
            return redirect()->route('admin.logs')
                ->with('error', 'Gagal menghapus logs: ' . $e->getMessage());

        }

    }

    public function searchLogs(Request $request) {

        try {

            $search = $request->get('search');

            $logs = Tblog::with('user')
                ->where('aktivitas', 'like', "%{$search}%")
                ->orWhereHas('user', function($query) use ($search) {

                    $query->where('name', 'like', "%{$search}%");

                })

                ->orderBy('tanggal', 'desc')
                ->paginate(20);

            return view('admin.logs', compact('logs', 'search'));

        } catch (\Exception $e) {

            \Log::error('Search logs error: ' . $e->getMessage());
            return redirect()->route('admin.logs')
                ->with('error', 'Gagal mencari logs: ' . $e->getMessage());

        }

    }

    public function getLogsStatistics() {

        try {

            $totalLogs = Tblog::count();
            $todayLogs = Tblog::whereDate('tanggal', today())->count();
            $userLogs = Tblog::whereNotNull('id_user')->count();
            $systemLogs = Tblog::whereNull('id_user')->count();

            return response()->json([

                'total_logs' => $totalLogs,
                'today_logs' => $todayLogs,
                'user_logs' => $userLogs,
                'system_logs' => $systemLogs,

            ]);

        } catch (\Exception $e) {

            \Log::error('Logs statistics error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal memuat statistics logs'], 500);

        }

    }

    public function getPaketStatistics() {

        try {

            $totalPakets = Paket::count();
            $paketsByJenis = Paket::groupBy('jenis')
                ->selectRaw('jenis, count(*) as total')
                ->get();
            $paketsByOutlet = Paket::with('outlet')
                ->groupBy('id_outlet')
                ->selectRaw('id_outlet, count(*) as total')
                ->get();

            return response()->json([

                'total_pakets' => $totalPakets,
                'pakets_by_jenis' => $paketsByJenis,
                'pakets_by_outlet' => $paketsByOutlet,

            ]);

        } catch (\Exception $e) {

            \Log::error('Paket statistics error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal memuat statistics paket'], 500);

        }

    }

    public function getUserStatistics() {

        try {

            $totalUsers = User::count();
            $usersByRole = User::groupBy('role')
                ->selectRaw('role, count(*) as total')
                ->get();
            $usersByOutlet = User::with('outlet')
                ->groupBy('id_outlet')
                ->selectRaw('id_outlet, count(*) as total')
                ->get();
            $todayUsers = User::whereDate('created_at', today())->count();

            return response()->json([

                'total_users' => $totalUsers,
                'users_by_role' => $usersByRole,
                'users_by_outlet' => $usersByOutlet,
                'today_users' => $todayUsers,

            ]);

        } catch (\Exception $e) {

            \Log::error('User statistics error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal memuat statistics users'], 500);
        }

    }

    public function getUsersData(Request $request) {

        try {

            $users = User::with('outlet')
                ->when($request->has('search'), function($query) use ($request) {

                    $search = $request->get('search');
                    return $query->where('name', 'like', "%{$search}%")
                                ->orWhere('username', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");

                })

                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return response()->json($users);

        } catch (\Exception $e) {

            \Log::error('Get users data error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal memuat data users'], 500);

        }

    }

    public function getUserChartData() {

        try {

            $monthlyUsers = User::selectRaw('

                YEAR(created_at) as year,
                MONTH(created_at) as month,
                COUNT(*) as total

            ')

            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

            $usersByRole = User::groupBy('role')
                ->selectRaw('role, count(*) as total')
                ->get();

            return response()->json([

                'monthly_users' => $monthlyUsers,
                'users_by_role' => $usersByRole,

            ]);

        } catch (\Exception $e) {

            \Log::error('User chart data error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal memuat chart data users'], 500);

        }

    }

    public function getTransactionStatistics() {

        try {

            $totalTransactions = Transaksi::count();
            $todayTransactions = Transaksi::whereDate('tgl', today())->count();
            $transactionsByStatus = Transaksi::groupBy('status')
                ->selectRaw('status, count(*) as total')
                ->get();
            $transactionsByPayment = Transaksi::groupBy('dibayar')
                ->selectRaw('dibayar, count(*) as total')
                ->get();

            $totalRevenue = Transaksi::where('dibayar', 'dibayar')
                ->get()

                ->reduce(function ($carry, $transaksi) {

                    $totalPaket = $transaksi->detailTransaksi->sum(function ($detail) {

                        return $detail->paket->harga * $detail->qty;

                    });

                    $total = $totalPaket + ($transaksi->biaya_tambahan ?? 0) - ($transaksi->diskon ?? 0) + ($transaksi->pajak ?? 0);
                    return $carry + $total;

                }, 0);

            return response()->json([

                'total_transactions' => $totalTransactions,
                'today_transactions' => $todayTransactions,
                'transactions_by_status' => $transactionsByStatus,
                'transactions_by_payment' => $transactionsByPayment,
                'total_revenue' => $totalRevenue,

            ]);

        } catch (\Exception $e) {

            \Log::error('Transaction statistics error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal memuat statistics transaksi'], 500);

        }

    }

    public function getTransactionChartData() {

        try {

            $monthlyTransactions = Transaksi::selectRaw('
                YEAR(tgl) as year,
                MONTH(tgl) as month,
                COUNT(*) as total
            ')
            ->where('tgl', '>=', now()->subMonths(6))
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

            $transactionsByStatus = Transaksi::groupBy('status')
                ->selectRaw('status, count(*) as total')
                ->get();

            $transactionsByOutlet = Transaksi::with('outlet')
                ->groupBy('id_outlet')
                ->selectRaw('id_outlet, count(*) as total')
                ->get();

            return response()->json([

                'monthly_transactions' => $monthlyTransactions,
                'transactions_by_status' => $transactionsByStatus,
                'transactions_by_outlet' => $transactionsByOutlet,

            ]);

        } catch (\Exception $e) {

            \Log::error('Transaction chart data error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal memuat chart data transaksi'], 500);

        }

    }

}
