<!DOCTYPE html>
<html>
<head>
    <title>Invoice - {{ $transaksi->kode_invoice }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                font-size: 12px;
            }
            .container {
                width: 100% !important;
                max-width: 100% !important;
            }
        }
        .invoice-header {
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .invoice-details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        .total-section {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">

        <div class="invoice-header">
            <div class="row">
                <div class="col-6">
                    <h2>TWINKLE WASH</h2>
                    <p class="mb-0">{{ $transaksi->outlet->nama }}</p>
                    <p class="mb-0">{{ $transaksi->outlet->alamat }}</p>
                    <p class="mb-0">Telp: {{ $transaksi->outlet->tlp }}</p>
                </div>
                <div class="col-6 text-end">
                    <h3>INVOICE</h3>
                    <p class="mb-0"><strong>{{ $transaksi->kode_invoice }}</strong></p>
                    <p class="mb-0">Tanggal: {{ $transaksi->tgl->format('d/m/Y H:i') }}</p>
                    <p class="mb-0">
                        Tipe:
                        <span class="badge
                            @if($transaksi->tipe_pelanggan === 'member') bg-info
                            @else bg-secondary @endif">
                            {{ $transaksi->tipe_pelanggan_formatted }}
                        </span>
                    </p>
                </div>
            </div>
        </div>


        <div class="row mb-4">
            <div class="col-md-6">
                <div class="invoice-details">
                    <h5>Informasi Pelanggan</h5>
                    <p class="mb-1"><strong>Nama:</strong> {{ $transaksi->nama_pelanggan_display }}</p>
                    <p class="mb-1"><strong>Alamat:</strong>
                        @if($transaksi->tipe_pelanggan === 'member' && $transaksi->member)
                            {{ $transaksi->member->alamat ?? '-' }}
                        @else
                            -
                        @endif
                    </p>
                    <p class="mb-1"><strong>Telepon:</strong> {{ $transaksi->tlp_pelanggan_display }}</p>
                    <p class="mb-0"><strong>Tipe:</strong> {{ $transaksi->tipe_pelanggan_formatted }}</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="invoice-details">
                    <h5>Informasi Transaksi</h5>
                    <p class="mb-1"><strong>Kasir:</strong> {{ $transaksi->user->name }}</p>
                    <p class="mb-1"><strong>Tanggal Transaksi:</strong> {{ $transaksi->tgl->format('d/m/Y H:i') }}</p>
                    <p class="mb-1"><strong>Batas Waktu:</strong> {{ $transaksi->batas_waktu->format('d/m/Y H:i') }}</p>
                    <p class="mb-1"><strong>Status:</strong>
                        <span class="badge
                            @if($transaksi->status == 'baru') bg-secondary
                            @elseif($transaksi->status == 'proses') bg-warning
                            @elseif($transaksi->status == 'selesai') bg-info
                            @elseif($transaksi->status == 'diambil') bg-success @endif">
                            {{ $transaksi->status_formatted }}
                        </span>
                    </p>
                    <p class="mb-0"><strong>Pembayaran:</strong>
                        <span class="badge
                            @if($transaksi->dibayar == 'dibayar') bg-success
                            @else bg-danger @endif">
                            {{ $transaksi->dibayar_formatted }}
                        </span>
                    </p>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-12">
                <h5>Detail Paket Laundry</h5>
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Paket</th>
                            <th>Jenis</th>
                            <th>Harga</th>
                            <th>Qty</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transaksi->detailTransaksi as $index => $detail)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $detail->nama_paket }}</td>
                            <td>{{ $detail->paket->jenis_formatted }}</td>
                            <td>Rp {{ number_format($detail->harga_paket, 0, ',', '.') }}</td>
                            <td>{{ $detail->qty }}</td>
                            <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>


        <div class="row">
            <div class="col-md-6 offset-md-6">
                <div class="total-section">
                    @php
                        $totalPaket = $transaksi->detailTransaksi->sum('subtotal');
                        $biayaTambahan = $transaksi->biaya_tambahan ?? 0;
                        $diskon = $transaksi->diskon ?? 0;
                        $pajak = $transaksi->pajak ?? 0;
                        $totalAkhir = $totalPaket + $biayaTambahan - $diskon + $pajak;
                    @endphp
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td>Total Paket:</td>
                            <td class="text-end">Rp {{ number_format($totalPaket, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Biaya Tambahan:</td>
                            <td class="text-end">Rp {{ number_format($biayaTambahan, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Diskon:</td>
                            <td class="text-end">- Rp {{ number_format($diskon, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Pajak:</td>
                            <td class="text-end">Rp {{ number_format($pajak, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="table-active">
                            <td><strong>TOTAL AKHIR:</strong></td>
                            <td class="text-end"><strong>Rp {{ number_format($totalAkhir, 0, ',', '.') }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>


        <div class="row mt-5">
            <div class="col-12 text-center">
                <div class="border-top pt-3">
                    <p class="text-muted mb-0">Terima kasih atas kepercayaan Anda menggunakan jasa laundry kami</p>
                    <p class="text-muted">* Simpan invoice ini sebagai bukti transaksi *</p>
                </div>
            </div>
        </div>


        <div class="row mt-3 no-print">
            <div class="col-12 text-center">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fas fa-print"></i> Print Invoice
                </button>
                <a href="{{ route('transaksi.show', $transaksi->id) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>

        window.onload = function() {

        };
        
    </script>
</body>
</html>
