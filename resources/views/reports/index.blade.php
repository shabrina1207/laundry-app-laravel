<!DOCTYPE html>
<html>
<head>
    <title>Generate Laporan - Backend Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1>Generate Laporan</h1>

        <a href="{{ route(auth()->user()->role . '.dashboard') }}" class="btn btn-secondary mb-3">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Pilih Jenis Laporan</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('reports.generate') }}" method="POST" id="reportForm">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="jenis_laporan" class="form-label">Jenis Laporan *</label>
                                <select name="jenis_laporan" id="jenis_laporan" class="form-select" required>
                                    <option value="">Pilih Jenis Laporan</option>
                                    <option value="per_outlet" {{ old('jenis_laporan') == 'per_outlet' ? 'selected' : '' }}>Transaksi Per Outlet</option>
                                    <option value="per_periode" {{ old('jenis_laporan') == 'per_periode' ? 'selected' : '' }}>Transaksi Per Periode</option>
                                    <option value="per_status" {{ old('jenis_laporan') == 'per_status' ? 'selected' : '' }}>Transaksi Per Status</option>
                                </select>
                                @error('jenis_laporan') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="format" class="form-label">Format Output *</label>
                                <select name="format" id="format" class="form-select" required>
                                    <option value="view" {{ old('format') == 'view' ? 'selected' : '' }}>Tampilkan di Web</option>
                                    <option value="pdf" {{ old('format') == 'pdf' ? 'selected' : '' }}>Download PDF</option>
                                    <option value="excel" {{ old('format') == 'excel' ? 'selected' : '' }}>Download Excel</option>
                                </select>
                                @error('format') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>


                    <div class="row mb-3 filter-section" id="outlet_filter" style="display: none;">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="outlet_id" class="form-label">Outlet</label>
                                <select name="outlet_id" id="outlet_id" class="form-select">
                                    <option value="">Semua Outlet</option>
                                    @foreach($outlets as $outlet)
                                        <option value="{{ $outlet->id }}" {{ old('outlet_id') == $outlet->id ? 'selected' : '' }}>
                                            {{ $outlet->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('outlet_id') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>


                    <div class="row mb-3 filter-section" id="periode_filter" style="display: none;">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="start_date" class="form-label">Tanggal Mulai</label>
                                <input type="date" name="start_date" id="start_date" class="form-control"
                                       value="{{ old('start_date', date('Y-m-01')) }}">
                                @error('start_date') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="end_date" class="form-label">Tanggal Akhir</label>
                                <input type="date" name="end_date" id="end_date" class="form-control"
                                       value="{{ old('end_date', date('Y-m-d')) }}">
                                @error('end_date') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>


                    <div class="row mb-3 filter-section" id="status_filter" style="display: none;">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status Transaksi</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="">Semua Status</option>
                                    <option value="baru" {{ old('status') == 'baru' ? 'selected' : '' }}>Baru</option>
                                    <option value="proses" {{ old('status') == 'proses' ? 'selected' : '' }}>Proses</option>
                                    <option value="selesai" {{ old('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                    <option value="diambil" {{ old('status') == 'diambil' ? 'selected' : '' }}>Diambil</option>
                                </select>
                                @error('status') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="dibayar" class="form-label">Status Pembayaran</label>
                                <select name="dibayar" id="dibayar" class="form-select">
                                    <option value="">Semua Status</option>
                                    <option value="dibayar" {{ old('dibayar') == 'dibayar' ? 'selected' : '' }}>Sudah Dibayar</option>
                                    <option value="belum_dibayar" {{ old('dibayar') == 'belum_dibayar' ? 'selected' : '' }}>Belum Dibayar</option>
                                </select>
                                @error('dibayar') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-chart-bar"></i> Generate Laporan
                        </button>
                        <button type="reset" class="btn btn-secondary btn-lg">Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>

        document.addEventListener('DOMContentLoaded', function() {

            const jenisLaporan = document.getElementById('jenis_laporan');
            const filterSections = document.querySelectorAll('.filter-section');


            function toggleFilters() {

                filterSections.forEach(section => {
                    section.style.display = 'none';
                });


                switch(jenisLaporan.value) {

                    case 'per_outlet':
                        document.getElementById('outlet_filter').style.display = 'flex';
                        break;
                    case 'per_periode':
                        document.getElementById('periode_filter').style.display = 'flex';
                        break;
                    case 'per_status':
                        document.getElementById('status_filter').style.display = 'flex';
                        break;

                }

            }

            jenisLaporan.addEventListener('change', toggleFilters);


            toggleFilters();

        });

    </script>
</body>
</html>
