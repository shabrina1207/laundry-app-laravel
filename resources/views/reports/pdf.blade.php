<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Laporan Transaksi' }}</title>
    <style>
        @import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }

        .container {
            width: 100%;
            max-width: 100%;
            padding: 10px;
        }

        .header {
            border-bottom: 3px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .company-info h2 {
            color: #2c3e50;
            margin: 0 0 5px 0;
            font-size: 20px;
        }

        .report-info h3 {
            color: #e74c3c;
            margin: 0 0 5px 0;
            font-size: 18px;
            text-align: right;
        }

        .summary-cards {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .summary-card {
            flex: 1;
            min-width: 200px;
            margin: 5px;
            padding: 10px;
            border-radius: 5px;
            color: white;
            text-align: center;
        }

        .card-primary { background-color: #3498db; }
        .card-success { background-color: #27ae60; }
        .card-warning { background-color: #f39c12; }
        .card-danger { background-color: #e74c3c; }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .table th {
            background-color: #2c3e50;
            color: white;
            padding: 8px;
            text-align: left;
            border: 1px solid #34495e;
            font-weight: bold;
        }

        .table td {
            padding: 8px;
            border: 1px solid #ddd;
        }

        .table-striped tbody tr:nth-child(odd) {
            background-color: #f8f9fa;
        }

        .table-hover tbody tr:hover {
            background-color: #e9ecef;
        }

        .badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            color: white;
        }

        .badge-secondary { background-color: #6c757d; }
        .badge-warning { background-color: #ffc107; color: #212529; }
        .badge-info { background-color: #17a2b8; }
        .badge-success { background-color: #28a745; }
        .badge-danger { background-color: #dc3545; }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-danger { color: #dc3545; }

        .summary-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            border-left: 4px solid #3498db;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #333;
            text-align: center;
            color: #6c757d;
        }

        .page-break {
            page-break-after: always;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            color: white;
        }

        .terlambat {
            background-color: #dc3545;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <div class="container">

        <div class="header">
            <div class="invoice-header">
                <div class="company-info">
                    <h2>TWINKLE WASH LAUNDRY</h2>
                    <p style="margin: 2px 0; font-weight: bold;">Sistem Manajemen Laundry</p>
                    <p style="margin: 2px 0; color: #666;">Laporan Transaksi Terintegrasi</p>
                </div>
                <div class="report-info">
                    <h3>{{ $title ?? 'Laporan Transaksi' }}</h3>
                    <p style="margin: 2px 0; text-align: right;"><strong>Dibuat pada:</strong> {{ date('d/m/Y H:i') }}</p>
                    <p style="margin: 2px 0; text-align: right;"><strong>Oleh:</strong> {{ Auth::user()->name ?? 'System' }} ({{ Auth::user()->role_formatted ?? 'User' }})</p>
                </div>
            </div>
        </div>

        <div class="summary-cards">
            <div class="summary-card card-primary">
                <h4 style="margin: 0 0 5px 0; font-size: 14px;">Total Transaksi</h4>
                <p style="margin: 0; font-size: 18px; font-weight: bold;">{{ $statistics['total_transaksi'] ?? 0 }}</p>
            </div>
            <div class="summary-card card-success">
                <h4 style="margin: 0 0 5px 0; font-size: 14px;">Total Pendapatan</h4>
                <p style="margin: 0; font-size: 18px; font-weight: bold;">Rp {{ number_format($statistics['total_pendapatan'] ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="summary-card card-warning">
                <h4 style="margin: 0 0 5px 0; font-size: 14px;">Sudah Dibayar</h4>
                <p style="margin: 0; font-size: 18px; font-weight: bold;">{{ $statistics['payment_count']['dibayar'] ?? 0 }}</p>
            </div>
            <div class="summary-card card-danger">
                <h4 style="margin: 0 0 5px 0; font-size: 14px;">Belum Dibayar</h4>
                <p style="margin: 0; font-size: 18px; font-weight: bold;">{{ $statistics['payment_count']['belum_dibayar'] ?? 0 }}</p>
            </div>
        </div>

        @if(!empty($filters))
        <div style="background-color: #e8f4fd; padding: 10px; border-radius: 5px; margin-bottom: 15px; border-left: 4px solid #3498db;">
            <h4 style="margin: 0 0 8px 0; color: #2c3e50; font-size: 14px;">Filter yang Digunakan:</h4>
            <div style="display: flex; flex-wrap: wrap; gap: 15px;">
                @if(isset($filters['jenis_laporan']))
                <div>
                    <strong>Jenis Laporan:</strong>
                    @if($filters['jenis_laporan'] == 'per_outlet') Transaksi Per Outlet
                    @elseif($filters['jenis_laporan'] == 'per_periode') Transaksi Per Periode
                    @elseif($filters['jenis_laporan'] == 'per_status') Transaksi Per Status
                    @endif
                </div>
                @endif

                @if(isset($filters['outlet_id']) && $filters['outlet_id'])
                <div>
                    <strong>Outlet:</strong>
                    @php
                        $outlet = \App\Models\Outlet::find($filters['outlet_id']);
                        echo $outlet ? $outlet->nama : 'Outlet Tidak Ditemukan';
                    @endphp
                </div>
                @endif

                @if(isset($filters['start_date']) && $filters['start_date'])
                <div>
                    <strong>Periode:</strong>
                    {{ date('d/m/Y', strtotime($filters['start_date'])) }} -
                    {{ date('d/m/Y', strtotime($filters['end_date'] ?? $filters['start_date'])) }}
                </div>
                @endif

                @if(isset($filters['status']) && $filters['status'])
                <div>
                    <strong>Status:</strong>
                    @if($filters['status'] == 'baru') Baru
                    @elseif($filters['status'] == 'proses') Proses
                    @elseif($filters['status'] == 'selesai') Selesai
                    @elseif($filters['status'] == 'diambil') Diambil
                    @endif
                </div>
                @endif

                @if(isset($filters['dibayar']) && $filters['dibayar'])
                <div>
                    <strong>Pembayaran:</strong>
                    @if($filters['dibayar'] == 'dibayar') Sudah Dibayar
                    @elseif($filters['dibayar'] == 'belum_dibayar') Belum Dibayar
                    @endif
                </div>
                @endif
            </div>
        </div>
        @endif

        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Invoice</th>
                    <th>Outlet</th>
                    <th>Member</th>
                    <th>Tanggal</th>
                    <th>Batas Waktu</th>
                    <th>Status</th>
                    <th>Pembayaran</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $transaction)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>
                        <strong>{{ $transaction->kode_invoice ?? 'Tidak Ada Invoice' }}</strong>
                        @if(isset($transaction->is_terlambat) && $transaction->is_terlambat)
                            <span class="terlambat">TERLAMBAT</span>
                        @endif
                    </td>
                    <td>{{ $transaction->outlet->nama ?? 'Outlet Tidak Ditemukan' }}</td>
                    <td>{{ $transaction->member->nama ?? 'Member Tidak Ditemukan' }}</td>
                    <td>{{ $transaction->tgl ? $transaction->tgl->format('d/m/Y H:i') : '-' }}</td>
                    <td>{{ $transaction->batas_waktu ? $transaction->batas_waktu->format('d/m/Y H:i') : '-' }}</td>
                    <td>
                        <span class="status-badge
                            @if($transaction->status == 'baru') badge-secondary
                            @elseif($transaction->status == 'proses') badge-warning
                            @elseif($transaction->status == 'selesai') badge-info
                            @elseif($transaction->status == 'diambil') badge-success @endif">
                            @if($transaction->status == 'baru') Baru
                            @elseif($transaction->status == 'proses') Proses
                            @elseif($transaction->status == 'selesai') Selesai
                            @elseif($transaction->status == 'diambil') Diambil
                            @else {{ $transaction->status }}
                            @endif
                        </span>
                    </td>
                    <td>
                        <span class="status-badge
                            @if($transaction->dibayar == 'dibayar') badge-success
                            @else badge-danger @endif">
                            @if($transaction->dibayar == 'dibayar') Lunas
                            @else Belum Bayar
                            @endif
                        </span>
                    </td>
                    <td class="text-right">
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
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary-section">
            <h4 style="margin: 0 0 15px 0; color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 5px;">Ringkasan Detail</h4>

            <div style="display: flex; justify-content: space-between; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 250px;">
                    <h5 style="margin: 0 0 10px 0; color: #495057;">Statistik Status</h5>
                    <table style="width: 100%;">
                        <tr>
                            <td>Status Baru:</td>
                            <td><strong>{{ $statistics['status_count']['baru'] ?? 0 }} transaksi</strong></td>
                        </tr>
                        <tr>
                            <td>Status Proses:</td>
                            <td><strong>{{ $statistics['status_count']['proses'] ?? 0 }} transaksi</strong></td>
                        </tr>
                        <tr>
                            <td>Status Selesai:</td>
                            <td><strong>{{ $statistics['status_count']['selesai'] ?? 0 }} transaksi</strong></td>
                        </tr>
                        <tr>
                            <td>Status Diambil:</td>
                            <td><strong>{{ $statistics['status_count']['diambil'] ?? 0 }} transaksi</strong></td>
                        </tr>
                    </table>
                </div>

                <div style="flex: 1; min-width: 250px;">
                    <h5 style="margin: 0 0 10px 0; color: #495057;">Ringkasan Keuangan</h5>
                    <table style="width: 100%;">
                        <tr>
                            <td>Total Transaksi:</td>
                            <td class="text-right"><strong>{{ $statistics['total_transaksi'] ?? 0 }}</strong></td>
                        </tr>
                        <tr>
                            <td>Total Pendapatan:</td>
                            <td class="text-right"><strong>Rp {{ number_format($statistics['total_pendapatan'] ?? 0, 0, ',', '.') }}</strong></td>
                        </tr>
                        <tr style="border-top: 1px solid #ddd;">
                            <td>Rata-rata per Transaksi:</td>
                            <td class="text-right">
                                <strong>
                                    Rp {{ number_format(($statistics['total_transaksi'] ?? 0) > 0 ? ($statistics['total_pendapatan'] ?? 0) / ($statistics['total_transaksi'] ?? 1) : 0, 0, ',', '.') }}
                                </strong>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="footer">
            <p style="margin: 5px 0; font-weight: bold;">Laporan ini dibuat secara otomatis oleh Sistem Twinkle Wash Laundry</p>
            <p style="margin: 5px 0; color: #999;">
                Dicetak pada: {{ date('d F Y H:i:s') }}
            </p>
            <p style="margin: 5px 0; color: #999; font-size: 10px;">
                &copy; {{ date('Y') }} Twinkle Wash Laundry - All rights reserved
            </p>
        </div>
    </div>
</body>
</html>
