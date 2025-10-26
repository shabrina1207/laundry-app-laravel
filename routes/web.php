<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\OutletController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\PaketController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\ReportController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {

    return view('welcome');

});

Route::get('/emergency-logout', function () {

    auth()->logout();
    session()->flush();
    return redirect('/login')->with('success', 'Berhasil logout');

});

Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

Route::middleware('guest')->group(function () {

    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {

    Route::get('/email/verify', function () {

        return view('auth.verify-email');

    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {

        $request->fulfill();
        return redirect('/dashboard')->with('success', 'Email berhasil diverifikasi!');

    })->middleware(['signed'])->name('verification.verify');

    Route::post('/email/verification-notification', [AuthController::class, 'resendVerification'])->name('verification.send');

});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile');
    Route::post('/profile/update', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/change-password', [AuthController::class, 'changePassword'])->name('profile.change-password');

});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::post('/api/check-paket-duplicate', [PaketController::class, 'checkDuplicate'])->name('api.check.paket.duplicate');
    Route::post('/api/check-phone-duplicate', [MemberController::class, 'checkDuplicatePhone'])->name('api.check.phone.duplicate');

    Route::get('/api/statistics', function () {

        return response()->json([

            'totalOutlets' => \App\Models\Outlet::count(),
            'totalMembers' => \App\Models\Member::count(),
            'totalUsers' => \App\Models\User::count(),
            'todayTransactions' => \App\Models\Transaksi::whereDate('tgl', today())->count(),
            'totalPakets' => \App\Models\Paket::count(),

        ]);

    })->name('api.statistics');

    Route::get('/api/outlets', function () {

        $outlets = \App\Models\Outlet::withCount('users')->get();
        return response()->json($outlets);

    })->name('api.outlets');

    Route::get('/api/pakets', function () {

        $pakets = \App\Models\Paket::with('outlet')->get();
        return response()->json($pakets);

    })->name('api.pakets');

    Route::get('/api/pakets/outlet/{outletId}', function ($outletId) {

        $pakets = \App\Models\Paket::where('id_outlet', $outletId)->get();
        return response()->json($pakets);

    })->name('api.pakets.by-outlet');

    Route::get('/api/transaksi/statistics', function () {

        $user = auth()->user();
        $data = [
            'totalTransaksi' => \App\Models\Transaksi::count(),
            'transaksiHariIni' => \App\Models\Transaksi::whereDate('tgl', today())->count(),
            'transaksiBaru' => \App\Models\Transaksi::where('status', 'baru')->count(),
            'transaksiDiproses' => \App\Models\Transaksi::where('status', 'proses')->count(),
            'transaksiSelesai' => \App\Models\Transaksi::where('status', 'selesai')->count(),
            'transaksiDiambil' => \App\Models\Transaksi::where('status', 'diambil')->count(),
            'totalPendapatan' => \App\Models\Transaksi::where('dibayar', 'dibayar')
                ->get()

                ->reduce(function ($carry, $transaksi) {

                    $totalPaket = $transaksi->detailTransaksi->sum(function ($detail) {

                        return $detail->qty * $detail->paket->harga;

                    });
                    $total = $totalPaket + ($transaksi->biaya_tambahan ?? 0) - ($transaksi->diskon ?? 0) + ($transaksi->pajak ?? 0);
                    return $carry + $total;

                }, 0),

        ];

        if ($user->id_outlet) {

            $data['totalTransaksi'] = \App\Models\Transaksi::where('id_outlet', $user->id_outlet)->count();
            $data['transaksiHariIni'] = \App\Models\Transaksi::where('id_outlet', $user->id_outlet)
                ->whereDate('tgl', today())->count();

        }

        return response()->json($data);

    })->name('api.transaksi.statistics');

    Route::get('/api/reports/statistics', function () {

        $user = auth()->user();

        $monthlyTransactions = \App\Models\Transaksi::whereMonth('tgl', now()->month)
            ->whereYear('tgl', now()->year)
            ->count();

        $monthlyRevenue = \App\Models\Transaksi::where('dibayar', 'dibayar')
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

        $completedTransactions = \App\Models\Transaksi::where('status', 'diambil')
            ->where('dibayar', 'dibayar')
            ->count();

        $averageTransaction = $monthlyTransactions > 0 ? $monthlyRevenue / $monthlyTransactions : 0;

        $data = [

            'monthlyTransactions' => $monthlyTransactions,
            'monthlyRevenue' => $monthlyRevenue,
            'completedTransactions' => $completedTransactions,
            'averageTransaction' => $averageTransaction,

        ];

        if ($user->id_outlet && !$user->isOwner) {

            $data['monthlyTransactions'] = \App\Models\Transaksi::where('id_outlet', $user->id_outlet)
                ->whereMonth('tgl', now()->month)
                ->whereYear('tgl', now()->year)
                ->count();

            $data['monthlyRevenue'] = \App\Models\Transaksi::where('id_outlet', $user->id_outlet)
                ->where('dibayar', 'dibayar')
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

        }

        return response()->json($data);

    })->name('api.reports.statistics');


    Route::get('/api/logs/count', function () {

        $count = \App\Models\Tblog::count();
        return response()->json(['count' => $count]);

    })->name('api.logs.count');

    Route::get('/api/logs/statistics', [AdminController::class, 'getLogsStatistics'])->name('api.logs.statistics');

    Route::get('/api/pakets/statistics', [AdminController::class, 'getPaketStatistics'])->name('api.pakets.statistics');

    Route::middleware(['role:admin'])->group(function () {

        Route::get('/api/users', function () {

            $users = \App\Models\User::with('outlet')->get();
            return response()->json($users);

        })->name('api.users');

        Route::get('/api/users/statistics', [AdminController::class, 'getUserStatistics'])->name('api.users.statistics');

        Route::get('/api/users/chart-data', [AdminController::class, 'getUserChartData'])->name('api.users.chart-data');

        Route::get('/api/users/data', [AdminController::class, 'getUsersData'])->name('api.users.data');

        Route::get('/api/members/statistics', function () {

            return response()->json([

                'totalMembers' => \App\Models\Member::count(),
                'membersByGender' => \App\Models\Member::groupBy('jenis_kelamin')
                    ->selectRaw('jenis_kelamin, count(*) as total')
                    ->get(),

            ]);

        })->name('api.members.statistics');

        Route::get('/api/outlets/statistics', function () {

            return response()->json([

                'totalOutlets' => \App\Models\Outlet::count(),
                'outletsWithUsers' => \App\Models\Outlet::has('users')->count(),
                'outletsWithTransactions' => \App\Models\Outlet::has('transaksi')->count(),
                'todayOutlets' => \App\Models\Outlet::whereDate('created_at', today())->count(),

            ]);

        })->name('api.outlets.statistics');

    });

    Route::get('/api/general-statistics', function () {

        $user = auth()->user();
        $data = [

            'totalOutlets' => \App\Models\Outlet::count(),
            'totalMembers' => \App\Models\Member::count(),
            'totalUsers' => \App\Models\User::count(),
            'todayTransactions' => \App\Models\Transaksi::whereDate('tgl', today())->count(),
            'totalPakets' => \App\Models\Paket::count(),

        ];

        if ($user->role === 'admin') {

            $data['usersByRole'] = \App\Models\User::groupBy('role')
                ->selectRaw('role, count(*) as total')
                ->get();
            $data['recentLogsCount'] = \App\Models\Tblog::whereDate('tanggal', today())->count();

        }

        if ($user->role === 'owner' && $user->id_outlet) {

            $data['myOutletMembers'] = \App\Models\Member::count();
            $data['myOutletTransactions'] = \App\Models\Transaksi::where('id_outlet', $user->id_outlet)
                ->whereDate('tgl', today())
                ->count();

        }

        return response()->json($data);

    })->name('api.general-statistics');

});


Route::middleware(['auth', 'verified', 'role:admin,kasir'])->group(function () {


    Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi.index');
    Route::get('/transaksi/create', [TransaksiController::class, 'create'])->name('transaksi.create');
    Route::post('/transaksi', [TransaksiController::class, 'store'])->name('transaksi.store');
    Route::get('/transaksi/{id}', [TransaksiController::class, 'show'])->name('transaksi.show');


    Route::post('/transaksi/{id}/update-status', [TransaksiController::class, 'updateStatus'])->name('transaksi.updateStatus');


    Route::post('/transaksi/{id}/update-status-quick', [TransaksiController::class, 'updateStatusQuick'])->name('transaksi.updateStatusQuick');

    Route::get('/transaksi/{id}/print', [TransaksiController::class, 'printInvoice'])->name('transaksi.print');
    Route::get('/api/paket/{outletId}', [TransaksiController::class, 'getPaketByOutlet'])->name('transaksi.getPaketByOutlet');

});


Route::middleware(['auth', 'verified', 'role:admin,kasir,owner'])->group(function () {

    Route::prefix('reports')->group(function () {

        Route::get('/', [ReportController::class, 'index'])->name('reports.index');
        Route::post('/generate', [ReportController::class, 'generate'])->name('reports.generate');

    });

});


Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {

    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    Route::resource('admin/users', UserController::class)->names([

        'index' => 'admin.users.index',
        'create' => 'admin.users.create',
        'store' => 'admin.users.store',
        'edit' => 'admin.users.edit',
        'update' => 'admin.users.update',
        'destroy' => 'admin.users.destroy'

    ]);

    Route::get('/admin/users-old', [AdminController::class, 'showUsers'])->name('admin.users');
    Route::put('/admin/users/{id}/update-role', [AdminController::class, 'updateUserRole'])->name('admin.users.update-role');
    Route::delete('/admin/users/{id}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');

    Route::get('/admin/settings', [AdminController::class, 'showSettings'])->name('admin.settings');
    Route::get('/admin/logs', [AdminController::class, 'showLogs'])->name('admin.logs');
    Route::post('/admin/logs/clear', [AdminController::class, 'clearLogs'])->name('admin.logs.clear');
    Route::get('/admin/logs/search', [AdminController::class, 'searchLogs'])->name('admin.logs.search');

    Route::get('/admin/outlets', [OutletController::class, 'index'])->name('admin.outlets.index');
    Route::get('/admin/outlets/create', [OutletController::class, 'create'])->name('admin.outlets.create');
    Route::post('/admin/outlets', [OutletController::class, 'store'])->name('admin.outlets.store');
    Route::get('/admin/outlets/{outlet}', [OutletController::class, 'show'])->name('admin.outlets.show');
    Route::get('/admin/outlets/{outlet}/edit', [OutletController::class, 'edit'])->name('admin.outlets.edit');
    Route::put('/admin/outlets/{outlet}', [OutletController::class, 'update'])->name('admin.outlets.update');
    Route::delete('/admin/outlets/{outlet}', [OutletController::class, 'destroy'])->name('admin.outlets.destroy');

    Route::get('/admin/members', [MemberController::class, 'index'])->name('admin.members.index');
    Route::get('/admin/members/create', [MemberController::class, 'create'])->name('admin.members.create');
    Route::post('/admin/members', [MemberController::class, 'store'])->name('admin.members.store');
    Route::get('/admin/members/{member}', [MemberController::class, 'show'])->name('admin.members.show');
    Route::get('/admin/members/{member}/edit', [MemberController::class, 'edit'])->name('admin.members.edit');
    Route::put('/admin/members/{member}', [MemberController::class, 'update'])->name('admin.members.update');
    Route::delete('/admin/members/{member}', [MemberController::class, 'destroy'])->name('admin.members.destroy');

    Route::get('/admin/paket', [PaketController::class, 'index'])->name('admin.paket.index');
    Route::get('/admin/paket/create', [PaketController::class, 'create'])->name('admin.paket.create');
    Route::post('/admin/paket', [PaketController::class, 'store'])->name('admin.paket.store');
    Route::get('/admin/paket/{paket}', [PaketController::class, 'show'])->name('admin.paket.show');
    Route::get('/admin/paket/{paket}/edit', [PaketController::class, 'edit'])->name('admin.paket.edit');
    Route::put('/admin/paket/{paket}', [PaketController::class, 'update'])->name('admin.paket.update');
    Route::delete('/admin/paket/{paket}', [PaketController::class, 'destroy'])->name('admin.paket.destroy');


    Route::prefix('admin')->group(function () {

        Route::get('/transaksi', [TransaksiController::class, 'index'])->name('admin.transaksi.index');
        Route::get('/transaksi/create', [TransaksiController::class, 'create'])->name('admin.transaksi.create');
        Route::post('/transaksi', [TransaksiController::class, 'store'])->name('admin.transaksi.store');
        Route::get('/transaksi/{id}', [TransaksiController::class, 'show'])->name('admin.transaksi.show');
        Route::post('/transaksi/{id}/update-status', [TransaksiController::class, 'updateStatus'])->name('admin.transaksi.updateStatus');


        Route::post('/transaksi/{id}/update-status-quick', [TransaksiController::class, 'updateStatusQuick'])->name('admin.transaksi.updateStatusQuick');

        Route::get('/transaksi/{id}/print', [TransaksiController::class, 'printInvoice'])->name('admin.transaksi.print');

    });


    Route::prefix('admin')->group(function () {

        Route::get('/reports', [ReportController::class, 'index'])->name('admin.reports.index');
        Route::post('/reports/generate', [ReportController::class, 'generate'])->name('admin.reports.generate');

    });
});


Route::middleware(['auth', 'verified', 'role:kasir'])->group(function () {

    Route::get('/kasir/dashboard', [KasirController::class, 'dashboard'])->name('kasir.dashboard');
    Route::get('/kasir/laporan', [KasirController::class, 'laporan'])->name('kasir.laporan');


    Route::prefix('kasir')->group(function () {

        Route::get('/transaksi', [TransaksiController::class, 'index'])->name('kasir.transaksi.index');
        Route::get('/transaksi/create', [TransaksiController::class, 'create'])->name('kasir.transaksi.create');
        Route::post('/transaksi', [TransaksiController::class, 'store'])->name('kasir.transaksi.store');
        Route::get('/transaksi/{id}', [TransaksiController::class, 'show'])->name('kasir.transaksi.show');
        Route::post('/transaksi/{id}/update-status', [TransaksiController::class, 'updateStatus'])->name('kasir.transaksi.updateStatus');


        Route::post('/transaksi/{id}/update-status-quick', [TransaksiController::class, 'updateStatusQuick'])->name('kasir.transaksi.updateStatusQuick');

        Route::get('/transaksi/{id}/print', [TransaksiController::class, 'printInvoice'])->name('kasir.transaksi.print');

    });

    Route::get('/kasir/members', [MemberController::class, 'index'])->name('kasir.members.index');
    Route::get('/kasir/members/create', [MemberController::class, 'create'])->name('kasir.members.create');
    Route::post('/kasir/members', [MemberController::class, 'store'])->name('kasir.members.store');
    Route::get('/kasir/members/{member}', [MemberController::class, 'show'])->name('kasir.members.show');
    Route::get('/kasir/members/{member}/edit', [MemberController::class, 'edit'])->name('kasir.members.edit');
    Route::put('/kasir/members/{member}', [MemberController::class, 'update'])->name('kasir.members.update');
    Route::delete('/kasir/members/{member}', [MemberController::class, 'destroy'])->name('kasir.members.destroy');

    Route::get('/kasir/paket', [PaketController::class, 'index'])->name('kasir.paket.index');
    Route::get('/kasir/paket/create', [PaketController::class, 'create'])->name('kasir.paket.create');
    Route::post('/kasir/paket', [PaketController::class, 'store'])->name('kasir.paket.store');
    Route::get('/kasir/paket/{paket}', [PaketController::class, 'show'])->name('kasir.paket.show');
    Route::get('/kasir/paket/{paket}/edit', [PaketController::class, 'edit'])->name('kasir.paket.edit');
    Route::put('/kasir/paket/{paket}', [PaketController::class, 'update'])->name('kasir.paket.update');
    Route::delete('/kasir/paket/{paket}', [PaketController::class, 'destroy'])->name('kasir.paket.destroy');


    Route::prefix('kasir')->group(function () {

        Route::get('/reports', [ReportController::class, 'index'])->name('kasir.reports.index');
        Route::post('/reports/generate', [ReportController::class, 'generate'])->name('kasir.reports.generate');


    });
});


Route::middleware(['auth', 'verified', 'role:owner'])->group(function () {

    Route::get('/owner/dashboard', [OwnerController::class, 'dashboard'])->name('owner.dashboard');

    Route::prefix('owner')->group(function () {

        Route::get('/reports', [ReportController::class, 'index'])->name('owner.reports.index');
        Route::post('/reports/generate', [ReportController::class, 'generate'])->name('owner.reports.generate');

    });

    Route::get('/owner/{any}', function () {

        return redirect()->route('owner.dashboard');

    })->where('any', '.*');
});


Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', function () {

        $user = auth()->user();

        switch ($user->role) {

            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'kasir':
                return redirect()->route('kasir.dashboard');
            case 'owner':
                return redirect()->route('owner.dashboard');
            default:
                abort(403, 'Role tidak dikenali.');

        }

    })->name('dashboard');

    Route::get('/home', function () {

        return redirect()->route('dashboard');

    })->name('home');

});


Route::get('/debug-user-role', function () {

    if (auth()->check()) {

        $user = auth()->user();
        return response()->json([

            'user_id' => $user->id,
            'name' => $user->name,
            'role' => $user->role,
            'email' => $user->email,
            'can_access_transaksi' => in_array($user->role, ['admin', 'kasir'])

        ]);

    }

    return response()->json(['message' => 'Not authenticated']);

});

Route::get('/debug-role-middleware', function () {

    try {

        if (auth()->check()) {

            $user = auth()->user();
            return response()->json([

                'status' => 'success',
                'user' => [
                    'name' => $user->name,
                    'role' => $user->role,
                    'email' => $user->email

                ]

            ]);

        }

        return "Silakan login terlebih dahulu";

    } catch (\Exception $e) {

        return response()->json([

            'status' => 'error',
            'message' => $e->getMessage()

        ], 500);

    }

})->middleware(['auth']);

Route::get('/check-user', function () {

    if (auth()->check()) {

        return response()->json([

            'user' => [

                'id' => auth()->user()->id,
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
                'role' => auth()->user()->role,
                'email_verified' => auth()->user()->hasVerifiedEmail(),
                'phone' => auth()->user()->phone,
                'address' => auth()->user()->address,
                'outlet' => auth()->user()->outlet ? auth()->user()->outlet->nama : 'Tidak ada outlet'

            ]

        ]);

    }

    return response()->json(['message' => 'Belum login']);

});

Route::fallback(function () {

    return view('errors.404');

});
