<!DOCTYPE html>
<html>
<head>
    <title>Owner Dashboard - Laporan</title>
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
        .stat-card {
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .report-card {
            border-left: 4px solid #007bff;
        }
        .quick-action-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .revenue-chart {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .chart-bar {
            display: flex;
            align-items: end;
            height: 120px;
            gap: 10px;
            padding: 10px 0;
        }
        .chart-item {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .chart-bar-item {
            background: linear-gradient(to top, #4e73df, #2e59d9);
            width: 30px;
            border-radius: 4px 4px 0 0;
            transition: all 0.3s ease;
        }
        .chart-bar-item:hover {
            background: linear-gradient(to top, #2e59d9, #224abe);
            transform: scale(1.05);
        }
        .chart-label {
            margin-top: 5px;
            font-size: 11px;
            color: #6c757d;
            text-align: center;
        }
        .chart-value {
            font-size: 10px;
            color: #495057;
            margin-top: 3px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">

        <div class="header-container">
            <div>
                <h1>Owner Dashboard - Laporan & Analisis</h1>
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
                <div class="card stat-card border-primary">
                    <div class="card-body text-center">
                        <h5><i class="fas fa-money-bill-wave text-primary"></i> Total Pendapatan</h5>
                        <h3>Rp {{ number_format($stats['totalRevenue'] ?? 0, 0, ',', '.') }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card border-success">
                    <div class="card-body text-center">
                        <h5><i class="fas fa-chart-line text-success"></i> Pendapatan Bulan Ini</h5>
                        <h3>Rp {{ number_format($stats['monthlyRevenue'] ?? 0, 0, ',', '.') }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card border-info">
                    <div class="card-body text-center">
                        <h5><i class="fas fa-users text-info"></i> Total Pelanggan</h5>
                        <h3>{{ $stats['totalMembers'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card border-warning">
                    <div class="card-body text-center">
                        <h5><i class="fas fa-receipt text-warning"></i> Total Transaksi</h5>
                        <h3>{{ $stats['totalTransactions'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>


        <div class="card mb-4 quick-action-card">
            <div class="card-body text-center">
                <h3 class="text-white"><i class="fas fa-chart-bar"></i> Sistem Laporan Terintegrasi</h3>
                <p class="text-white mb-3">Akses lengkap semua laporan bisnis Anda</p>
                <a href="{{ route('owner.reports.index') }}" class="btn btn-light btn-lg">
                    <i class="fas fa-analytics"></i> Buka Menu Laporan
                </a>
            </div>
        </div>


        @if(!empty($weeklyRevenue) && count($weeklyRevenue) > 0)
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5><i class="fas fa-chart-line"></i> Pendapatan 7 Hari Terakhir</h5>
            </div>
            <div class="card-body">
                <div class="revenue-chart">
                    <div class="chart-bar">
                        @foreach($weeklyRevenue as $day)
                        <div class="chart-item">
                            <div class="chart-bar-item" style="height: {{ max(10, ($day['revenue'] / max(100000, collect($weeklyRevenue)->max('revenue'))) * 100) }}px;"
                                 title="Rp {{ number_format($day['revenue'], 0, ',', '.') }}"></div>
                            <div class="chart-value">Rp {{ number_format($day['revenue'] / 1000, 0) }}k</div>
                            <div class="chart-label">{{ \Carbon\Carbon::parse($day['date'])->format('d/m') }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="text-center text-muted">
                    <small>Total 7 Hari: Rp {{ number_format(collect($weeklyRevenue)->sum('revenue'), 0, ',', '.') }}</small>
                </div>
            </div>
        </div>
        @endif


        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5><i class="fas fa-bolt"></i> Laporan Cepat</h5>
            </div>
            <div class="card-body">
                <div class="row">

                    <div class="col-md-4 mb-3">
                        <div class="card h-100 report-card">
                            <div class="card-body text-center">
                                <i class="fas fa-calendar fa-2x text-primary mb-3"></i>
                                <h5>Laporan Harian</h5>
                                <p class="text-muted">Laporan transaksi hari ini</p>
                                <form action="{{ route('owner.reports.quick') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="jenis_laporan" value="per_periode">
                                    <input type="hidden" name="start_date" value="{{ today()->format('Y-m-d') }}">
                                    <input type="hidden" name="end_date" value="{{ today()->format('Y-m-d') }}">
                                    <input type="hidden" name="format" value="view">
                                    <button type="submit" class="btn btn-primary">
                                        Generate
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="card h-100 report-card">
                            <div class="card-body text-center">
                                <i class="fas fa-calendar-week fa-2x text-success mb-3"></i>
                                <h5>Laporan Mingguan</h5>
                                <p class="text-muted">Laporan transaksi minggu ini</p>
                                <form action="{{ route('owner.reports.quick') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="jenis_laporan" value="per_periode">
                                    <input type="hidden" name="start_date" value="{{ now()->startOfWeek()->format('Y-m-d') }}">
                                    <input type="hidden" name="end_date" value="{{ now()->endOfWeek()->format('Y-m-d') }}">
                                    <input type="hidden" name="format" value="view">
                                    <button type="submit" class="btn btn-success">
                                        Generate
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="card h-100 report-card">
                            <div class="card-body text-center">
                                <i class="fas fa-calendar-alt fa-2x text-info mb-3"></i>
                                <h5>Laporan Bulanan</h5>
                                <p class="text-muted">Laporan transaksi bulan ini</p>
                                <form action="{{ route('owner.reports.quick') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="jenis_laporan" value="per_periode">
                                    <input type="hidden" name="start_date" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                                    <input type="hidden" name="end_date" value="{{ now()->endOfMonth()->format('Y-m-d') }}">
                                    <input type="hidden" name="format" value="view">
                                    <button type="submit" class="btn btn-info">
                                        Generate
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="row">

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="fas fa-history"></i> Transaksi Terbaru</h5>
                        <span class="badge bg-primary">{{ $recentTransactions->count() }}</span>
                    </div>
                    <div class="card-body">
                        @if($recentTransactions->count() > 0)
                            @foreach($recentTransactions as $transaksi)
                            <div class="border-bottom pb-2 mb-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong>{{ $transaksi->kode_invoice }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $transaksi->member->nama ?? 'N/A' }} - {{ $transaksi->outlet->nama }}</small>
                                        <br>
                                        <small class="text-muted">{{ $transaksi->tgl->format('d/m/Y H:i') }}</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge
                                            @if($transaksi->dibayar == 'dibayar') bg-success @else bg-danger @endif">
                                            {{ $transaksi->dibayar }}
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            @php
                                                $total = $transaksi->detailTransaksi->sum('subtotal')
                                                    + ($transaksi->biaya_tambahan ?? 0)
                                                    - ($transaksi->diskon ?? 0)
                                                    + ($transaksi->pajak ?? 0);
                                            @endphp
                                            Rp {{ number_format($total, 0, ',', '.') }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="text-center py-3">
                                <i class="fas fa-receipt fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">Tidak ada transaksi</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>


            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="fas fa-trophy"></i> Outlet Teratas</h5>
                        <span class="badge bg-warning">{{ $topOutlets->count() }}</span>
                    </div>
                    <div class="card-body">
                        @if($topOutlets->count() > 0)
                            @foreach($topOutlets as $outlet)
                            <div class="border-bottom pb-2 mb-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong>{{ $outlet->nama }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $outlet->total_transactions }} transaksi</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-primary">
                                            Rp {{ number_format($outlet->total_revenue ?? 0, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="text-center py-3">
                                <i class="fas fa-store fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">Tidak ada data outlet</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>


        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h6><i class="fas fa-clock text-warning"></i> Menunggu Diproses</h6>
                        <h3 class="text-warning">{{ $stats['pendingTransactions'] ?? 0 }}</h3>
                        <small class="text-muted">Transaksi status baru</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h6><i class="fas fa-cog text-info"></i> Sedang Diproses</h6>
                        <h3 class="text-info">{{ $stats['processingTransactions'] ?? 0 }}</h3>
                        <small class="text-muted">Transaksi status proses</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h6><i class="fas fa-money-bill-wave text-danger"></i> Belum Dibayar</h6>
                        <h3 class="text-danger">{{ $stats['pendingPayments'] ?? 0 }}</h3>
                        <small class="text-muted">Transaksi belum lunas</small>
                    </div>
                </div>
            </div>
        </div>


        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6><i class="fas fa-info-circle"></i> Informasi Akses</h6>
                        <p class="mb-0">
                            Sebagai <strong>Owner</strong>, Anda memiliki akses penuh ke semua laporan bisnis.
                            Gunakan menu laporan untuk menganalisis performa outlet, pendapatan, dan tren transaksi.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>

        document.addEventListener('DOMContentLoaded', function() {
            const bars = document.querySelectorAll('.chart-bar-item');
            bars.forEach((bar, index) => {

                const finalHeight = bar.style.height;
                bar.style.height = '0px';


                setTimeout(() => {
                    bar.style.height = finalHeight;
                }, index * 100);
            });
        });
    </script>
</body>
</html>
