<!DOCTYPE html>
<html>
<head>
    <title>Detail Transaksi - Backend Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .badge-kiloan {
            background-color: #ffc107;
            color: #000;
        }
        .badge-selimut {
            background-color: #0d6efd;
             color: #fff;
        }
        .badge-bed_cover {
             background-color: #198754;
             color: #fff;
        }
        .badge-kaos {
             background-color: #0dcaf0;
              color: #000;
             }
        .badge-lain {
             background-color: #6c757d;
             color: #fff;
        }
        .quantity-display {
             font-weight: 600;
        }
        .kiloan-quantity {
            color: #856404;
            background-color: #fff3cd;
            padding: 2px 8px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>Detail Transaksi: {{ $transaksi->kode_invoice }}</h1>
            <div>
                <a href="{{ route('transaksi.print', $transaksi->id) }}"
                   class="btn btn-secondary" target="_blank">
                    <i class="fas fa-print"></i> Print Invoice
                </a>
                <a href="{{ route('transaksi.index') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Informasi Transaksi</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tr>
                                <td width="40%"><strong>Kode Invoice</strong></td>
                                <td>{{ $transaksi->kode_invoice }}</td>
                            </tr>
                            <tr>
                                <td><strong>Outlet</strong></td>
                                <td>{{ $transaksi->outlet->nama }}</td>
                            </tr>
                            <tr>
                                <td><strong>Tipe Pelanggan</strong></td>
                                <td>
                                    <span class="badge
                                        @if($transaksi->tipe_pelanggan === 'member') bg-info
                                        @else bg-secondary @endif">
                                        {{ $transaksi->tipe_pelanggan_formatted }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Pelanggan</strong></td>
                                <td>{{ $transaksi->nama_pelanggan_display }}</td>
                            </tr>
                            <tr>
                                <td><strong>Telepon</strong></td>
                                <td>{{ $transaksi->tlp_pelanggan_display }}</td>
                            </tr>
                            <tr>
                                <td><strong>Kasir</strong></td>
                                <td>{{ $transaksi->user->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal Transaksi</strong></td>
                                <td>{{ $transaksi->tgl->format('d/m/Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Batas Waktu</strong></td>
                                <td class="@if($transaksi->is_terlambat) text-danger @endif">
                                    {{ $transaksi->batas_waktu->format('d/m/Y H:i') }}
                                    @if($transaksi->is_terlambat)
                                        <span class="badge bg-danger">Terlambat</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal Bayar</strong></td>
                                <td>
                                    @if($transaksi->tgl_bayar)
                                        {{ $transaksi->tgl_bayar->format('d/m/Y H:i') }}
                                    @else
                                        <span class="text-muted">Belum bayar</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Status & Pembayaran</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('transaksi.updateStatus', $transaksi->id) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="baru" {{ $transaksi->status == 'baru' ? 'selected' : '' }}>Baru</option>
                                    <option value="proses" {{ $transaksi->status == 'proses' ? 'selected' : '' }}>Diproses</option>
                                    <option value="selesai" {{ $transaksi->status == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                    <option value="diambil" {{ $transaksi->status == 'diambil' ? 'selected' : '' }}>Diambil</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="dibayar" class="form-label">Status Pembayaran</label>
                                <select name="dibayar" id="dibayar" class="form-select">
                                    <option value="belum_dibayar" {{ $transaksi->dibayar == 'belum_dibayar' ? 'selected' : '' }}>Belum Dibayar</option>
                                    <option value="dibayar" {{ $transaksi->dibayar == 'dibayar' ? 'selected' : '' }}>Sudah Dibayar</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-sync-alt"></i> Update Status
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Detail Paket</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Paket</th>
                                <th>Jenis</th>
                                <th>Harga</th>
                                <th>Quantity</th>
                                <th>Keterangan</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transaksi->detailTransaksi as $detail)
                                <tr>
                                    <td>{{ $detail->nama_paket }}</td>
                                    <td>
                                        @php
                                            $jenis = $detail->paket->jenis;
                                            $badgeClass = 'badge-' . $jenis;
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">
                                            {{ $detail->paket->jenis_formatted }}
                                        </span>
                                    </td>
                                    <td>{{ $detail->harga_paket_formatted }}</td>
                                    <td>
                                        @if($detail->paket->jenis === 'kiloan' && $detail->berat)
                                            <span class="quantity-display kiloan-quantity">
                                                <i class="fas fa-weight-hanging me-1"></i>
                                                {{ number_format($detail->berat, 1) }} kg
                                            </span>
                                            <br>
                                            <small class="text-muted">
                                                (Qty: {{ number_format($detail->qty, 1) }})
                                            </small>
                                        @else
                                            <span class="quantity-display">
                                                {{ number_format($detail->qty, 1) }} pcs
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{ $detail->keterangan ?? '-' }}</td>
                                    <td>{{ $detail->subtotal_formatted }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            @php

                                $totalPaket = 0;
                                foreach ($transaksi->detailTransaksi as $detail) {
                                    $totalPaket += $detail->subtotal;
                                }

                                $biayaTambahan = $transaksi->biaya_tambahan ?? 0;
                                $diskon = $transaksi->diskon ?? 0;
                                $pajak = $transaksi->pajak ?? 0;


                                $diskonRupiah = $totalPaket * $diskon;
                                $totalAkhir = $totalPaket + $biayaTambahan - $diskonRupiah + $pajak;
                            @endphp
                            <tr>
                                <td colspan="5" class="text-end"><strong>Total Paket:</strong></td>
                                <td><strong>Rp {{ number_format($totalPaket, 0, ',', '.') }}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end">Biaya Tambahan:</td>
                                <td>+ Rp {{ number_format($biayaTambahan, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end">Diskon ({{ number_format($diskon * 100, 0) }}%):</td>
                                <td>- Rp {{ number_format($diskonRupiah, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end">Pajak:</td>
                                <td>+ Rp {{ number_format($pajak, 0, ',', '.') }}</td>
                            </tr>
                            <tr class="table-primary">
                                <td colspan="5" class="text-end"><strong>Total Akhir:</strong></td>
                                <td><strong>Rp {{ number_format($totalAkhir, 0, ',', '.') }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>


        <div class="card mt-4">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Paket</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Keterangan Jenis Paket:</h6>
                        <ul class="list-unstyled small">
                            <li>
                                <span class="badge badge-kiloan me-2">Kiloan</span>
                                <span>Ditampilkan dalam <strong>kilogram (kg)</strong> dengan latar kuning</span>
                            </li>
                            <li>
                                <span class="badge badge-selimut me-2">Selimut</span>
                                <span>Ditampilkan dalam <strong>piece (pcs)</strong></span>
                            </li>
                            <li>
                                <span class="badge badge-bed_cover me-2">Bed Cover</span>
                                <span>Ditampilkan dalam <strong>piece (pcs)</strong></span>
                            </li>
                            <li>
                                <span class="badge badge-kaos me-2">Kaos</span>
                                <span>Ditampilkan dalam <strong>piece (pcs)</strong></span>
                            </li>
                            <li>
                                <span class="badge badge-lain me-2">Lainnya</span>
                                <span>Ditampilkan dalam <strong>piece (pcs)</strong></span>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Catatan:</h6>
                        <ul class="list-unstyled small">
                            <li><i class="fas fa-weight-hanging text-warning me-2"></i>Paket <strong>kiloan</strong> menampilkan <strong>berat aktual</strong> dalam kg</li>
                            <li><i class="fas fa-cube text-secondary me-2"></i>Paket <strong>non-kiloan</strong> menampilkan <strong>quantity</strong> dalam piece</li>
                            <li>Subtotal dihitung berdasarkan: <strong>Harga × Quantity/berat</strong></li>
                            <li>Untuk paket kiloan, quantity asli tetap disimpan sebagai backup</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
