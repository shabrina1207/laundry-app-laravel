<!DOCTYPE html>
<html>
<head>
    <title>Tambah Paket - Twinkle Wash</title>
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
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Tambah Paket Baru</h4>
                    </div>
                    <div class="card-body">
                        <a href="{{ route(auth()->user()->role . '.paket.index') }}" class="btn btn-secondary mb-3">
                            <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar Paket
                        </a>

                        <form action="{{ route(auth()->user()->role . '.paket.store') }}" method="POST" id="paketForm">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="id_outlet" class="form-label required">Outlet</label>
                                        <select name="id_outlet" id="id_outlet" class="form-select" required>
                                            <option value="">Pilih Outlet</option>
                                            @foreach($outlets as $outlet)
                                                <option value="{{ $outlet->id }}" {{ old('id_outlet') == $outlet->id ? 'selected' : '' }}>
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
                                            <option value="kiloan" {{ old('jenis') == 'kiloan' ? 'selected' : '' }}>Kiloan</option>
                                            <option value="selimut" {{ old('jenis') == 'selimut' ? 'selected' : '' }}>Selimut</option>
                                            <option value="bed_cover" {{ old('jenis') == 'bed_cover' ? 'selected' : '' }}>Bed Cover</option>
                                            <option value="kaos" {{ old('jenis') == 'kaos' ? 'selected' : '' }}>Kaos</option>
                                            <option value="lain" {{ old('jenis') == 'lain' ? 'selected' : '' }}>Lainnya</option>
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
                                       value="{{ old('nama_paket') }}"
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
                                                   value="{{ old('harga') }}"
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
                                            Rp 0
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                <button type="reset" class="btn btn-outline-secondary me-md-2">
                                    <i class="fas fa-undo me-1"></i> Reset
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Simpan Paket
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>

    document.addEventListener('DOMContentLoaded', function() {

        const hargaInput = document.getElementById('harga');
        const pricePreview = document.getElementById('pricePreview');
        const namaPaketInput = document.getElementById('nama_paket');
        const outletSelect = document.getElementById('id_outlet');
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


        namaPaketInput.addEventListener('blur', function() {

            const namaPaket = this.value;
            const outletId = outletSelect.value;

            if (namaPaket && outletId) {

                console.log('Validasi duplikat:', namaPaket, outletId);

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
