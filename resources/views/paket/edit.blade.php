<!DOCTYPE html>
<html>
<head>
    <title>Edit Paket - Twinkle Wash</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid rgba(0, 0, 0, 0.125);
        }
        .form-label {
            font-weight: 600;
            color: #495057;
        }
        .required::after {
            content: " *";
            color: #dc3545;
        }
        .price-preview {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            font-weight: 600;
            color: #28a745;
            text-align: center;
        }
        .package-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Paket: {{ $paket->nama_paket }}</h4>
                    </div>
                    <div class="card-body">
                        <a href="{{ route(auth()->user()->role . '.paket.index') }}" class="btn btn-secondary mb-3">
                            <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar Paket
                        </a>


                        <div class="package-info p-3 mb-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong><i class="fas fa-tag me-2"></i>Nama Paket:</strong><br>
                                    {{ $paket->nama_paket }}
                                </div>
                                <div class="col-md-3">
                                    <strong><i class="fas fa-list me-2"></i>Jenis:</strong><br>
                                    {{ ucfirst($paket->jenis) }}
                                </div>
                                <div class="col-md-3">
                                    <strong><i class="fas fa-store me-2"></i>Outlet:</strong><br>
                                    {{ $paket->outlet->nama }}
                                </div>
                            </div>
                        </div>

                        <form action="{{ route(auth()->user()->role . '.paket.update', $paket) }}" method="POST" id="paketForm">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="id_outlet" class="form-label required">Outlet</label>
                                        <select name="id_outlet" id="id_outlet" class="form-select" required>
                                            <option value="">Pilih Outlet</option>
                                            @foreach($outlets as $outlet)
                                                <option value="{{ $outlet->id }}" {{ old('id_outlet', $paket->id_outlet) == $outlet->id ? 'selected' : '' }}>
                                                    {{ $outlet->nama }} - {{ $outlet->alamat }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('id_outlet')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="jenis" class="form-label required">Jenis Paket</label>
                                        <select name="jenis" id="jenis" class="form-select" required>
                                            <option value="">Pilih Jenis Paket</option>
                                            <option value="kiloan" {{ old('jenis', $paket->jenis) == 'kiloan' ? 'selected' : '' }}>Kiloan</option>
                                            <option value="selimut" {{ old('jenis', $paket->jenis) == 'selimut' ? 'selected' : '' }}>Selimut</option>
                                            <option value="bed_cover" {{ old('jenis', $paket->jenis) == 'bed_cover' ? 'selected' : '' }}>Bed Cover</option>
                                            <option value="kaos" {{ old('jenis', $paket->jenis) == 'kaos' ? 'selected' : '' }}>Kaos</option>
                                            <option value="lain" {{ old('jenis', $paket->jenis) == 'lain' ? 'selected' : '' }}>Lainnya</option>
                                        </select>
                                        @error('jenis')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="nama_paket" class="form-label required">Nama Paket</label>
                                <input type="text" name="nama_paket" id="nama_paket"
                                       class="form-control"
                                       value="{{ old('nama_paket', $paket->nama_paket) }}"
                                       required
                                       maxlength="100"
                                       placeholder="Contoh: Cuci Kiloan Reguler">
                                <div class="form-text">Maksimal 100 karakter</div>
                                @error('nama_paket')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="harga" class="form-label required">Harga Paket</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" name="harga" id="harga"
                                                   class="form-control"
                                                   value="{{ old('harga', $paket->harga) }}"
                                                   required
                                                   min="1000"
                                                   max="10000000"
                                                   step="1000"
                                                   placeholder="15000">
                                        </div>
                                        <div class="form-text">
                                            Harga minimal: Rp 1.000, maksimal: Rp 10.000.000
                                        </div>
                                        @error('harga')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Preview Harga</label>
                                        <div class="price-preview" id="pricePreview">
                                            Rp {{ number_format($paket->harga, 0, ',', '.') }}
                                        </div>
                                        <div class="form-text text-center">
                                            <small>Harga sebelumnya: Rp {{ number_format($paket->harga, 0, ',', '.') }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                <a href="{{ route(auth()->user()->role . '.paket.index') }}" class="btn btn-outline-secondary me-md-2">
                                    <i class="fas fa-times me-1"></i> Batal
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Update Paket
                                </button>
                            </div>
                        </form>
                    </div>
                </div>


                <div class="card mt-4 border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Perhatian</h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning mb-0">
                            <h6><i class="fas fa-info-circle me-2"></i>Informasi Penting:</h6>
                            <ul class="mb-0">
                                <li>Perubahan harga akan mempengaruhi transaksi baru</li>
                                <li>Transaksi yang sudah dibuat tidak akan terpengaruh oleh perubahan harga</li>
                                <li>Pastikan outlet yang dipilih sudah benar</li>
                                <li>Harga yang diubah akan langsung berlaku untuk transaksi baru</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>

    document.addEventListener('DOMContentLoaded', function() {

        const hargaInput = document.getElementById('harga');
        const pricePreview = document.getElementById('pricePreview');
        const form = document.getElementById('paketForm');


        hargaInput.addEventListener('input', function() {

            const harga = this.value ? parseInt(this.value) : 0;
            pricePreview.textContent = formatRupiah(harga);


            if (harga < 1000) {

                pricePreview.style.color = '#dc3545';

            } else if (harga > 10000000) {

                pricePreview.style.color = '#dc3545';

            } else {

                pricePreview.style.color = '#28a745';

            }

        });


        form.addEventListener('submit', function(e) {

            const harga = parseInt(hargaInput.value);

            if (harga < 1000) {

                e.preventDefault();
                alert('Harga minimal Rp 1.000');
                hargaInput.focus();

                return;

            }

            if (harga > 10000000) {

                e.preventDefault();

                alert('Harga maksimal Rp 10.000.000');
                hargaInput.focus();

                return;

            }

        });


        function formatRupiah(angka) {

            if (!angka) return 'Rp 0';

            return 'Rp ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');

        }


        if (hargaInput.value) {

            hargaInput.dispatchEvent(new Event('input'));

        }

    });
    
    </script>
</body>
</html>
