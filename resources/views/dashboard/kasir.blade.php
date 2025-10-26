<!DOCTYPE html>
<html>
<head>
    <title>Kasir Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
        }
        .user-info {
            margin-bottom: 10px;
        }
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">

        <div class="header-container">
            <div>
                <h1>Kasir Dashboard</h1>
                <p class="user-info">Welcome, {{ auth()->user()->name }} ({{ auth()->user()->role }})</p>
            </div>
            <div>

                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif


        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5><i class="fas fa-users"></i> Members</h5>
                        <h3>{{ $totalMembers }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5><i class="fas fa-cash-register"></i> Transactions Today</h5>
                        <h3>{{ $todayTransactions }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5><i class="fas fa-receipt"></i> Total Transaksi</h5>
                        <h3>{{ $totalTransactions }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5><i class="fas fa-clock"></i> Pending</h5>
                        <h3>{{ $pendingTransactions }}</h3>
                    </div>
                </div>
            </div>
        </div>


        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="fas fa-bolt"></i> Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">

                    <div class="col-md-4 mb-2">
                        <a href="{{ route('kasir.members.index') }}" class="btn btn-primary w-100 mb-1">
                            <i class="fas fa-users"></i> Data Pelanggan
                        </a>
                        <a href="{{ route('kasir.members.create') }}" class="btn btn-outline-primary w-100">
                            <i class="fas fa-plus"></i> Registrasi Pelanggan
                        </a>
                    </div>


                    <div class="col-md-4 mb-2">
                        <a href="{{ route('transaksi.index') }}" class="btn btn-success w-100 mb-1">
                            <i class="fas fa-cash-register"></i> Transaksi
                        </a>
                        <a href="{{ route('transaksi.create') }}" class="btn btn-outline-success w-100">
                            <i class="fas fa-plus"></i> Entri Transaksi
                        </a>
                    </div>


                    <div class="col-md-4 mb-2">
                        <a href="{{ route('kasir.laporan') }}" class="btn btn-warning w-100 mb-1">
                            <i class="fas fa-chart-bar"></i> Laporan
                        </a>
                        {{-- <a href="{{ route('kasir.laporan') }}?start_date={{ today()->format('Y-m-d') }}&end_date={{ today()->format('Y-m-d') }}" class="btn btn-outline-warning w-100">
                            <i class="fas fa-file-pdf"></i> Quick Report
                        </a> --}}
                    </div>
                </div>
            </div>
        </div>


        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="fas fa-chart-bar"></i> Quick Report Generation</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-calendar fa-2x text-primary mb-3"></i>
                                <h5>Laporan Harian</h5>
                                <p class="text-muted">Laporan transaksi hari ini</p>
                                <a href="{{ route('kasir.laporan') }}?start_date={{ today()->format('Y-m-d') }}&end_date={{ today()->format('Y-m-d') }}" class="btn btn-primary btn-sm">
                                    Generate
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-calendar-week fa-2x text-success mb-3"></i>
                                <h5>Laporan Mingguan</h5>
                                <p class="text-muted">Laporan transaksi minggu ini</p>
                                <a href="{{ route('kasir.laporan') }}?start_date={{ now()->startOfWeek()->format('Y-m-d') }}&end_date={{ now()->endOfWeek()->format('Y-m-d') }}" class="btn btn-success btn-sm">
                                    Generate
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-tasks fa-2x text-info mb-3"></i>
                                <h5>Laporan Status</h5>
                                <p class="text-muted">Laporan berdasarkan status</p>
                                <a href="{{ route('kasir.laporan') }}?status=baru" class="btn btn-info btn-sm">
                                    Generate
                                </a>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-3">
                                        <small class="text-muted">Transaksi Hari Ini</small>
                                        <h5 class="mb-0">{{ $todayTransactions }}</h5>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">Menunggu Diproses</small>
                                        <h5 class="mb-0">{{ $pendingTransactions }}</h5>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">Sedang Diproses</small>
                                        <h5 class="mb-0">{{ $processingTransactions }}</h5>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">Total Pelanggan</small>
                                        <h5 class="mb-0">{{ $totalMembers }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="row">

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h5><i class="fas fa-users"></i> Recent Members</h5>
                        <a href="{{ route('kasir.members.index') }}" class="btn btn-sm btn-outline-primary">
                            View All
                        </a>
                    </div>
                    <div class="card-body">
                        @if($recentMembers->count() > 0)
                            @foreach($recentMembers as $member)
                            <div class="border-bottom pb-2 mb-2">
                                <strong>{{ $member->nama }}</strong><br>
                                <small>{{ $member->tlp ?? 'No Phone' }}</small><br>
                                <small class="text-muted">{{ $member->created_at?->diffForHumans() ?? 'Unknown' }}</small>
                            </div>
                            @endforeach
                        @else
                            <p class="text-muted">No members</p>
                        @endif
                    </div>
                </div>
            </div>


            <div class="col-md-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h5><i class="fas fa-cash-register"></i> Recent Transactions</h5>
                        <a href="{{ route('transaksi.index') }}" class="btn btn-sm btn-outline-success">
                            View All
                        </a>
                    </div>
                    <div class="card-body">
                        @if($recentTransactions->count() > 0)
                            @foreach($recentTransactions as $transaksi)
                            <div class="border-bottom pb-2 mb-2">
                                <strong>{{ $transaksi->kode_invoice }}</strong><br>
                                <small>{{ $transaksi->member->nama ?? 'N/A' }}</small><br>
                                <span class="badge
                                    @if($transaksi->status == 'baru') bg-secondary
                                    @elseif($transaksi->status == 'proses') bg-warning
                                    @elseif($transaksi->status == 'selesai') bg-info
                                    @elseif($transaksi->status == 'diambil') bg-success @endif">
                                    {{ $transaksi->status }}
                                </span><br>
                                <small class="text-muted">{{ $transaksi->tgl->diffForHumans() }}</small>
                            </div>
                            @endforeach
                        @else
                            <p class="text-muted">No transactions</p>
                        @endif
                    </div>
                </div>
            </div>


            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-pie"></i> Quick Stats</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="border-end">
                                    <h4 class="text-primary">{{ $todayTransactions }}</h4>
                                    <small class="text-muted">Today</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <h4 class="text-warning">{{ $pendingTransactions }}</h4>
                                <small class="text-muted">Pending</small>
                            </div>
                            <div class="col-6">
                                <div class="border-end">
                                    <h4 class="text-info">{{ $processingTransactions }}</h4>
                                    <small class="text-muted">Processing</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <h4 class="text-success">{{ $completedTransactions ?? 0 }}</h4>
                                <small class="text-muted">Completed</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="row mt-4">

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h5><i class="fas fa-history"></i> Recent Transactions Detail</h5>
                        <div>
                            <a href="{{ route('transaksi.create') }}" class="btn btn-sm btn-success">
                                <i class="fas fa-plus"></i> New
                            </a>
                            <a href="{{ route('transaksi.index') }}" class="btn btn-sm btn-outline-danger">
                                View All
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($recentTransactions->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>Invoice</th>
                                            <th>Member</th>
                                            <th>Tanggal</th>
                                            <th>Status</th>
                                            <th>Pembayaran</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentTransactions as $transaksi)
                                        <tr class="@if($transaksi->is_terlambat) table-danger @endif">
                                            <td>
                                                <small class="fw-bold">{{ $transaksi->kode_invoice }}</small>
                                                @if($transaksi->is_terlambat)
                                                    <span class="badge bg-danger">Terlambat</span>
                                                @endif
                                            </td>
                                            <td>{{ $transaksi->member->nama ?? 'N/A' }}</td>
                                            <td>{{ $transaksi->tgl->format('d/m H:i') }}</td>
                                            <td>
                                                <span class="badge
                                                    @if($transaksi->status == 'baru') bg-secondary
                                                    @elseif($transaksi->status == 'proses') bg-warning
                                                    @elseif($transaksi->status == 'selesai') bg-info
                                                    @elseif($transaksi->status == 'diambil') bg-success @endif">
                                                    {{ $transaksi->status }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge
                                                    @if($transaksi->dibayar == 'dibayar') bg-success
                                                    @else bg-danger @endif">
                                                    {{ $transaksi->dibayar }}
                                                </span>
                                            </td>
                                            <td>
                                                @php
                                                    $total = $transaksi->detailTransaksi->sum('subtotal')
                                                        + ($transaksi->biaya_tambahan ?? 0)
                                                        - ($transaksi->diskon ?? 0)
                                                        + ($transaksi->pajak ?? 0);
                                                @endphp
                                                <strong>Rp {{ number_format($total, 0, ',', '.') }}</strong>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Belum ada transaksi</p>
                                <a href="{{ route('transaksi.create') }}" class="btn btn-success">
                                    <i class="fas fa-plus"></i> Buat Transaksi Pertama
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>


            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-bell"></i> Notifications</h5>
                    </div>
                    <div class="card-body">
                        @if($pendingTransactions > 0)
                            <div class="alert alert-warning alert-dismissible fade show py-2" role="alert">
                                <i class="fas fa-exclamation-circle"></i>
                                Ada <strong>{{ $pendingTransactions }}</strong> transaksi menunggu diproses
                            </div>
                        @endif

                        @if($recentTransactions->where('is_terlambat', true)->count() > 0)
                            <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
                                <i class="fas fa-clock"></i>
                                Ada <strong>{{ $recentTransactions->where('is_terlambat', true)->count() }}</strong> transaksi terlambat
                            </div>
                        @endif

                        @if($todayTransactions == 0)
                            <div class="alert alert-info alert-dismissible fade show py-2" role="alert">
                                <i class="fas fa-info-circle"></i>
                                Belum ada transaksi hari ini
                            </div>
                        @endif

                        @if($pendingTransactions == 0 && $recentTransactions->where('is_terlambat', true)->count() == 0 && $todayTransactions > 0)
                            <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
                                <i class="fas fa-check-circle"></i>
                                Semua transaksi dalam kondisi baik
                            </div>
                        @endif


                        <div class="mt-3">
                            <h6>Quick Actions:</h6>
                            <div class="d-grid gap-2">
                                <a href="{{ route('transaksi.create') }}" class="btn btn-success btn-sm">
                                    <i class="fas fa-plus"></i> Buat Transaksi Baru
                                </a>
                                <a href="{{ route('kasir.members.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-user-plus"></i> Daftarkan Pelanggan
                                </a>
                                <a href="{{ route('kasir.laporan') }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-file-alt"></i> Lihat Laporan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
