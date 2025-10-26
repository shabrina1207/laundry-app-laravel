<!DOCTYPE html>
<html>
<head>
    <title>Hasil Laporan - Backend Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>{{ $title ?? 'Laporan Transaksi' }}</h1>
            <div>
                <a href="{{ route('reports.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">{{ $statistics['total_transaksi'] ?? 0 }}</h4>
                                <p class="card-text">Total Transaksi</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-receipt fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">Rp {{ number_format($statistics['total_pendapatan'] ?? 0, 0, ',', '.') }}</h4>
                                <p class="card-text">Total Pendapatan</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-money-bill-wave fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">{{ $statistics['payment_count']['dibayar'] ?? 0 }}</h4>
                                <p class="card-text">Sudah Dibayar</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">{{ $statistics['payment_count']['belum_dibayar'] ?? 0 }}</h4>
                                <p class="card-text">Belum Dibayar</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Data Transaksi</h5>
                <div>
                    <span class="badge bg-info">{{ $transactions->count() }} transaksi ditemukan</span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Invoice</th>
                                <th>Outlet</th>
                                <th>Member</th>
                                <th>Tanggal</th>
                                <th>Batas Waktu</th>
                                <th>Status</th>
                                <th>Pembayaran</th>
                                <th>Total</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $transaction)
                            <tr class="@if(isset($transaction->is_terlambat) && $transaction->is_terlambat) table-danger @endif">
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <strong>{{ $transaction->kode_invoice ?? 'Tidak Ada Invoice' }}</strong>
                                    @if(isset($transaction->is_terlambat) && $transaction->is_terlambat)
                                        <span class="badge bg-danger">Terlambat</span>
                                    @endif
                                </td>
                                <td>{{ $transaction->outlet->nama ?? 'Outlet Tidak Ditemukan' }}</td>
                                <td>{{ $transaction->member->nama ?? 'Member Tidak Ditemukan' }}</td>
                                <td>{{ $transaction->tgl ? $transaction->tgl->format('d/m/Y H:i') : '-' }}</td>
                                <td>{{ $transaction->batas_waktu ? $transaction->batas_waktu->format('d/m/Y H:i') : '-' }}</td>
                                <td>
                                    <span class="badge
                                        @if($transaction->status == 'baru') bg-secondary
                                        @elseif($transaction->status == 'proses') bg-warning
                                        @elseif($transaction->status == 'selesai') bg-info
                                        @elseif($transaction->status == 'diambil') bg-success @endif">
                                        {{ $transaction->status_formatted ?? $transaction->status }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge
                                        @if($transaction->dibayar == 'dibayar') bg-success
                                        @else bg-danger @endif">
                                        {{ $transaction->dibayar_formatted ?? ($transaction->dibayar == 'dibayar' ? 'Lunas' : 'Belum Bayar') }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $subtotal = 0;
                                        if ($transaction->detailTransaksi) {
                                            $subtotal = $transaction->detailTransaksi->sum(function($detail) {
                                                if ($detail->paket) {
                                                    return $detail->qty * $detail->paket->harga;
                                                }
                                                return $detail->qty * 0;
                                            });
                                        }
                                        $total = $subtotal
                                            + ($transaction->biaya_tambahan ?? 0)
                                            - ($transaction->diskon ?? 0)
                                            + ($transaction->pajak ?? 0);
                                    @endphp
                                    <strong>Rp {{ number_format($total, 0, ',', '.') }}</strong>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('transaksi.show', $transaction->id) }}"
                                           class="btn btn-info" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('transaksi.print', $transaction->id) }}"
                                           class="btn btn-secondary" title="Print" target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <i class="fas fa-info-circle fa-2x text-muted mb-2"></i>
                                    <p class="text-muted">Tidak ada data transaksi yang sesuai dengan filter</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($transactions->count() > 0)
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Ringkasan Status</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td>Status Baru:</td>
                                        <td><span class="badge bg-secondary">{{ $statistics['status_count']['baru'] ?? 0 }}</span></td>
                                    </tr>
                                    <tr>
                                        <td>Status Proses:</td>
                                        <td><span class="badge bg-warning">{{ $statistics['status_count']['proses'] ?? 0 }}</span></td>
                                    </tr>
                                    <tr>
                                        <td>Status Selesai:</td>
                                        <td><span class="badge bg-info">{{ $statistics['status_count']['selesai'] ?? 0 }}</span></td>
                                    </tr>
                                    <tr>
                                        <td>Status Diambil:</td>
                                        <td><span class="badge bg-success">{{ $statistics['status_count']['diambil'] ?? 0 }}</span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Total Keseluruhan</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td>Total Transaksi:</td>
                                        <td><strong>{{ $statistics['total_transaksi'] ?? 0 }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Total Pendapatan:</td>
                                        <td><strong>Rp {{ number_format($statistics['total_pendapatan'] ?? 0, 0, ',', '.') }}</strong></td>
                                    </tr>
                                    <tr class="table-primary">
                                        <td>Rata-rata per Transaksi:</td>
                                        <td><strong>Rp {{ number_format(($statistics['total_transaksi'] ?? 0) > 0 ? ($statistics['total_pendapatan'] ?? 0) / ($statistics['total_transaksi'] ?? 1) : 0, 0, ',', '.') }}</strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        @if($transactions->count() > 0)
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <form action="{{ route('reports.generate') }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="jenis_laporan" value="{{ $filters['jenis_laporan'] ?? '' }}">
                        <input type="hidden" name="format" value="pdf">
                        <input type="hidden" name="outlet_id" value="{{ $filters['outlet_id'] ?? '' }}">
                        <input type="hidden" name="start_date" value="{{ $filters['start_date'] ?? '' }}">
                        <input type="hidden" name="end_date" value="{{ $filters['end_date'] ?? '' }}">
                        <input type="hidden" name="status" value="{{ $filters['status'] ?? '' }}">
                        <input type="hidden" name="dibayar" value="{{ $filters['dibayar'] ?? '' }}">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-file-pdf"></i> Download PDF
                        </button>
                    </form>
                    <form action="{{ route('reports.generate') }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="jenis_laporan" value="{{ $filters['jenis_laporan'] ?? '' }}">
                        <input type="hidden" name="format" value="excel">
                        <input type="hidden" name="outlet_id" value="{{ $filters['outlet_id'] ?? '' }}">
                        <input type="hidden" name="start_date" value="{{ $filters['start_date'] ?? '' }}">
                        <input type="hidden" name="end_date" value="{{ $filters['end_date'] ?? '' }}">
                        <input type="hidden" name="status" value="{{ $filters['status'] ?? '' }}">
                        <input type="hidden" name="dibayar" value="{{ $filters['dibayar'] ?? '' }}">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-file-excel"></i> Download Excel
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
