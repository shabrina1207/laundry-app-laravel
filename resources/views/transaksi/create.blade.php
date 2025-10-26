<!DOCTYPE html>
<html>
<head>
    <title>Entri Transaksi Baru - Twinkle Wash</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid rgba(0, 0, 0, 0.125);
            margin-bottom: 1rem;
        }
        .form-label {
            font-weight: 600;
            color: #495057;
        }
        .required::after {
            content: " *";
            color: #dc3545;
        }
        .price-display {
            font-weight: 600;
            color: #28a745;
        }
        .summary-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .package-item {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
            margin-bottom: 1rem;
            background: #f8f9fa;
        }
        .remove-package {
            color: #dc3545;
            cursor: pointer;
        }
        .remove-package:hover {
            color: #bd2130;
        }
        .field-required::after {
            content: " *";
            color: #dc3545;
        }
        .auto-tax {
            background-color: #f8f9fa !important;
            color: #6c757d;
        }
        .kiloan-field {
            background-color: #fff3cd !important;
            border-color: #ffeaa7;
        }
        .jenis-badge {
            font-size: 0.75em;
            padding: 0.25em 0.5em;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0"><i class="fas fa-cash-register me-2"></i>Entri Transaksi Baru</h1>
                    <a href="{{ route('transaksi.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar Transaksi
                    </a>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger">
                        <h5><i class="fas fa-exclamation-triangle me-2"></i>Terjadi Kesalahan:</h5>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('transaksi.store') }}" method="POST" id="transaksiForm">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Transaksi</h5>
                                </div>
                                <div class="card-body">
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

                                    <div class="mb-3">
                                        <label for="tipe_pelanggan" class="form-label required">Tipe Pelanggan</label>
                                        <select name="tipe_pelanggan" id="tipe_pelanggan" class="form-select" required>
                                            <option value="">Pilih Tipe Pelanggan</option>
                                            <option value="member" {{ old('tipe_pelanggan') == 'member' ? 'selected' : '' }}>Member</option>
                                            <option value="biasa" {{ old('tipe_pelanggan') == 'biasa' ? 'selected' : '' }}>Pelanggan Biasa</option>
                                        </select>
                                        @error('tipe_pelanggan')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3 d-none" id="member-field">
                                        <label for="id_member" class="form-label field-required">Member</label>
                                        <select name="id_member" id="id_member" class="form-select">
                                            <option value="">Pilih Member</option>
                                            @foreach($members as $member)
                                                <option value="{{ $member->id }}" {{ old('id_member') == $member->id ? 'selected' : '' }}>
                                                    {{ $member->nama }} - {{ $member->tlp }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('id_member')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3 d-none" id="biasa-fields">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="nama_pelanggan" class="form-label field-required">Nama Pelanggan</label>
                                                <input type="text" name="nama_pelanggan" id="nama_pelanggan"
                                                       class="form-control" value="{{ old('nama_pelanggan') }}"
                                                       maxlength="100"
                                                       placeholder="Masukkan nama pelanggan">
                                                @error('nama_pelanggan')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label for="tlp_pelanggan" class="form-label">Telepon</label>
                                                <input type="text" name="tlp_pelanggan" id="tlp_pelanggan"
                                                       class="form-control" value="{{ old('tlp_pelanggan') }}"
                                                       maxlength="15"
                                                       pattern="[0-9]+"
                                                       title="Hanya angka yang diperbolehkan"
                                                       placeholder="Masukkan nomor telepon">
                                                @error('tlp_pelanggan')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="durasi" class="form-label required">Durasi Pengerjaan (hari)</label>
                                        <input type="number" name="durasi" id="durasi" class="form-control"
                                               value="{{ old('durasi', 2) }}" min="1" max="30" required>
                                        <div class="form-text">Estimasi hari pengerjaan (1-30 hari)</div>
                                        @error('durasi')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Biaya Tambahan</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="biaya_tambahan" class="form-label">Biaya Tambahan</label>
                                        <input type="number" name="biaya_tambahan" id="biaya_tambahan"
                                               class="form-control"
                                               value="{{ old('biaya_tambahan', 0) }}"
                                               min="0" max="1000000" step="1000"
                                               placeholder="0">
                                        <div class="form-text">Biaya tambahan selain paket</div>
                                        @error('biaya_tambahan')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="diskon" class="form-label">Diskon (%)</label>
                                        <input type="number" name="diskon" id="diskon"
                                               class="form-control"
                                               value="{{ old('diskon', 0) }}"
                                               min="0" max="100" step="0.5"
                                               placeholder="0">
                                        <div class="form-text">Masukkan dalam persen (contoh: 5 untuk 5%)</div>
                                        @error('diskon')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="pajak" class="form-label">
                                            Pajak
                                            <span class="badge bg-info ms-1">Otomatis</span>
                                        </label>
                                        <input type="number" name="pajak" id="pajak"
                                               class="form-control auto-tax"
                                               value="1000"
                                               min="0" max="1000000" step="1000"
                                               placeholder="0" readonly>
                                        <input type="hidden" name="pajak_otomatis" id="pajak_otomatis" value="1000">
                                        <div class="form-text">
                                            <i class="fas fa-calculator me-1"></i>
                                            Pajak otomatis: <span id="display-pajak-otomatis" class="fw-bold">Rp 1.000</span>
                                            <small class="text-muted">(10% dari total paket, minimal Rp 1.000)</small>
                                        </div>
                                        @error('pajak')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-box me-2"></i>Paket Laundry</h5>
                            <button type="button" class="btn btn-light btn-sm" id="tambah-paket">
                                <i class="fas fa-plus me-1"></i> Tambah Paket
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="paket-container">

                            </div>
                            @error('id_paket')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            @error('qty')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            @error('berat')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="card summary-card">
                        <div class="card-header bg-transparent text-white">
                            <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Ringkasan Biaya</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-white">
                                <div class="col-md-3 text-center">
                                    <h6>Total Biaya Paket</h6>
                                    <h4 id="total-biaya-paket" class="price-display">Rp 0</h4>
                                </div>
                                <div class="col-md-2 text-center">
                                    <h6>Biaya Tambahan</h6>
                                    <div id="display-biaya-tambahan" class="price-display">Rp 0</div>
                                </div>
                                <div class="col-md-2 text-center">
                                    <h6>Diskon</h6>
                                    <div id="display-diskon" class="price-display">0%</div>
                                </div>
                                <div class="col-md-2 text-center">
                                    <h6>Pajak</h6>
                                    <div id="display-pajak" class="price-display">Rp 1.000</div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <h6>Total Akhir</h6>
                                    <h3 id="total-akhir" class="price-display">Rp 1.000</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <button type="reset" class="btn btn-outline-secondary me-md-2" id="resetBtn">
                            <i class="fas fa-undo me-1"></i> Reset Form
                        </button>
                        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                            <i class="fas fa-save me-1"></i> Simpan Transaksi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <template id="paket-template">
        <div class="package-item">
            <div class="row align-items-end">
                <div class="col-md-4">
                    <label class="form-label required">Paket</label>
                    <select name="id_paket[]" class="form-select paket-select" required>
                        <option value="">Pilih Paket</option>
                    </select>
                    <div class="jenis-info mt-1"></div>
                </div>


                <div class="col-md-2 d-none kiloan-field-container">
                    <label class="form-label required">Berat (kg)</label>
                    <input type="number" name="berat[]" class="form-control berat-input kiloan-field"
                           step="0.1" min="0.1" max="100" value="1" required
                           placeholder="1.5">
                    <small class="form-text">Berat dalam kilogram</small>
                </div>


                <div class="col-md-2 qty-field-container">
                    <label class="form-label required">Qty</label>
                    <input type="number" name="qty[]" class="form-control qty-input"
                           step="0.1" min="0.1" max="100" value="1" required>
                    <small class="form-text">Jumlah item</small>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Keterangan</label>
                    <input type="text" name="keterangan[]" class="form-control"
                           placeholder="Opsional (cuci saja, setrika saja)"
                           maxlength="255">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Subtotal</label>
                    <input type="text" class="form-control subtotal" readonly value="Rp 0">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-package mt-1 w-100">
                        <i class="fas fa-trash me-1"></i> Hapus
                    </button>
                </div>
            </div>
        </div>
    </template>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        
    document.addEventListener('DOMContentLoaded', function() {

        const tipePelangganSelect = document.getElementById('tipe_pelanggan');
        const memberField = document.getElementById('member-field');
        const biasaFields = document.getElementById('biasa-fields');
        const memberSelect = document.getElementById('id_member');
        const namaPelangganInput = document.getElementById('nama_pelanggan');
        const tlpPelangganInput = document.getElementById('tlp_pelanggan');
        const paketContainer = document.getElementById('paket-container');
        const paketTemplate = document.getElementById('paket-template');
        const tambahPaketBtn = document.getElementById('tambah-paket');
        const form = document.getElementById('transaksiForm');
        const resetBtn = document.getElementById('resetBtn');
        const submitBtn = document.getElementById('submitBtn');

        function togglePelangganFields() {

            const tipe = tipePelangganSelect.value;

            if (tipe === 'member') {

                memberField.classList.remove('d-none');
                biasaFields.classList.add('d-none');
                memberSelect.required = true;
                namaPelangganInput.required = false;
                tlpPelangganInput.required = false;

                namaPelangganInput.value = '';
                tlpPelangganInput.value = '';

            } else if (tipe === 'biasa') {

                memberField.classList.add('d-none');
                biasaFields.classList.remove('d-none');
                memberSelect.required = false;
                namaPelangganInput.required = true;
                tlpPelangganInput.required = false;

                memberSelect.value = '';

            } else {

                memberField.classList.add('d-none');
                biasaFields.classList.add('d-none');
                memberSelect.required = false;
                namaPelangganInput.required = false;
                tlpPelangganInput.required = false;

                memberSelect.value = '';
                namaPelangganInput.value = '';
                tlpPelangganInput.value = '';

            }

        }

        function toggleKiloanFields(paketSelect) {

            const item = paketSelect.closest('.package-item');
            const jenisInfo = item.querySelector('.jenis-info');
            const kiloanFieldContainer = item.querySelector('.kiloan-field-container');
            const qtyFieldContainer = item.querySelector('.qty-field-container');
            const beratInput = item.querySelector('.berat-input');
            const qtyInput = item.querySelector('.qty-input');

            const selectedOption = paketSelect.selectedOptions[0];
            const isKiloan = selectedOption && selectedOption.dataset.jenis === 'kiloan';

            if (isKiloan) {

                kiloanFieldContainer.classList.remove('d-none');
                qtyFieldContainer.classList.add('d-none');


                jenisInfo.innerHTML = '<span class="badge bg-warning text-dark jenis-badge"><i class="fas fa-weight-hanging me-1"></i>Kiloan - Masukkan berat dalam kg</span>';


                beratInput.required = true;
                qtyInput.required = false;


                if (!beratInput.value || beratInput.value === '1') {

                    beratInput.value = qtyInput.value;

                }

            } else {

                kiloanFieldContainer.classList.add('d-none');
                qtyFieldContainer.classList.remove('d-none');


                const jenis = selectedOption ? selectedOption.dataset.jenis : '';
                let badgeClass = 'bg-secondary';
                let icon = 'fas fa-cube';

                switch(jenis) {

                    case 'selimut':
                        badgeClass = 'bg-primary';
                        icon = 'fas fa-bed';
                        break;
                    case 'bed_cover':
                        badgeClass = 'bg-success';
                        icon = 'fas fa-bed';
                        break;
                    case 'kaos':
                        badgeClass = 'bg-info';
                        icon = 'fas fa-tshirt';
                        break;
                    case 'lain':
                        badgeClass = 'bg-dark';
                        icon = 'fas fa-ellipsis-h';
                        break;

                }

                jenisInfo.innerHTML = selectedOption ?
                    `<span class="badge ${badgeClass} jenis-badge"><i class="${icon} me-1"></i>${jenis.charAt(0).toUpperCase() + jenis.slice(1)}</span>` :
                    '';


                beratInput.required = false;
                qtyInput.required = true;

            }

        }

        function formatRupiah(angka) {

            if (!angka) return 'Rp 0';
            return 'Rp ' + parseInt(angka).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');

        }

        function hitungPajakOtomatis(totalBiayaPaket) {

            const pajakPersen = 0.10;
            let pajak = totalBiayaPaket * pajakPersen;

            if (pajak < 1000) {

                pajak = 1000;

            }

            pajak = Math.ceil(pajak / 1000) * 1000;

            return pajak;

        }

        function addPaketItem() {

            const newItem = paketTemplate.content.cloneNode(true);
            paketContainer.appendChild(newItem);
            attachEventListeners(paketContainer.lastElementChild);
            updatePaketOptions(paketContainer.lastElementChild);

        }

        function removePaketItem(element) {

            element.closest('.package-item').remove();
            hitungTotal();

        }

        function attachEventListeners(element) {

            const paketSelect = element.querySelector('.paket-select');
            const qtyInput = element.querySelector('.qty-input');
            const beratInput = element.querySelector('.berat-input');
            const removeBtn = element.querySelector('.remove-package');

            paketSelect.addEventListener('change', function() {

                toggleKiloanFields(this);
                hitungSubtotal(this);

            });

            qtyInput.addEventListener('input', function() {

                hitungSubtotal(this);

            });

            beratInput.addEventListener('input', function() {

                hitungSubtotal(this);

            });

            removeBtn.addEventListener('click', function() {

                removePaketItem(this);

            });

        }

        function hitungSubtotal(element) {

            const item = element.closest('.package-item');
            const paketSelect = item.querySelector('.paket-select');
            const qtyInput = item.querySelector('.qty-input');
            const beratInput = item.querySelector('.berat-input');
            const subtotalInput = item.querySelector('.subtotal');

            const selectedOption = paketSelect.selectedOptions[0];
            const harga = selectedOption?.dataset.harga || 0;
            const isKiloan = selectedOption?.dataset.jenis === 'kiloan';

            let quantity = 0;

            if (isKiloan) {

                quantity = parseFloat(beratInput.value) || 0;

            } else {

                quantity = parseFloat(qtyInput.value) || 0;

            }

            const subtotal = harga * quantity;
            subtotalInput.value = formatRupiah(subtotal);
            hitungTotal();

        }

        function hitungTotal() {

            let totalBiayaPaket = 0;

            document.querySelectorAll('.package-item').forEach(item => {

                const paketSelect = item.querySelector('.paket-select');
                const qtyInput = item.querySelector('.qty-input');
                const beratInput = item.querySelector('.berat-input');

                const selectedOption = paketSelect.selectedOptions[0];
                const harga = selectedOption?.dataset.harga || 0;
                const isKiloan = selectedOption?.dataset.jenis === 'kiloan';

                let quantity = 0;

                if (isKiloan) {

                    quantity = parseFloat(beratInput.value) || 0;

                } else {

                    quantity = parseFloat(qtyInput.value) || 0;

                }

                totalBiayaPaket += harga * quantity;

            });

            const biayaTambahan = parseFloat(document.getElementById('biaya_tambahan').value) || 0;
            const diskon = parseFloat(document.getElementById('diskon').value) || 0;
            const pajakOtomatis = hitungPajakOtomatis(totalBiayaPaket);

            document.getElementById('pajak').value = pajakOtomatis;
            document.getElementById('pajak_otomatis').value = pajakOtomatis;
            document.getElementById('display-pajak-otomatis').textContent = formatRupiah(pajakOtomatis);

            const totalSetelahDiskon = totalBiayaPaket - (totalBiayaPaket * (diskon / 100));
            const totalAkhir = totalSetelahDiskon + biayaTambahan + pajakOtomatis;

            document.getElementById('total-biaya-paket').textContent = formatRupiah(totalBiayaPaket);
            document.getElementById('display-biaya-tambahan').textContent = formatRupiah(biayaTambahan);
            document.getElementById('display-diskon').textContent = diskon + '%';
            document.getElementById('display-pajak').textContent = formatRupiah(pajakOtomatis);
            document.getElementById('total-akhir').textContent = formatRupiah(totalAkhir);

        }

        function updatePaketOptions(element) {

            const outletId = document.getElementById('id_outlet').value;
            const paketSelect = element.querySelector('.paket-select');

            if (outletId) {

                fetch(`/api/paket/${outletId}`)
                    .then(response => response.json())

                    .then(pakets => {

                        paketSelect.innerHTML = '<option value="">Pilih Paket</option>';

                        pakets.forEach(paket => {

                            const option = document.createElement('option');
                            option.value = paket.id;
                            option.textContent = `${paket.nama_paket} - ${formatRupiah(paket.harga)}`;
                            option.dataset.harga = paket.harga;
                            option.dataset.jenis = paket.jenis;
                            paketSelect.appendChild(option);

                        });

                    })

                    .catch(error => console.error('Error:', error));

            }

        }


        tipePelangganSelect.addEventListener('change', togglePelangganFields);
        tambahPaketBtn.addEventListener('click', addPaketItem);

        document.getElementById('id_outlet').addEventListener('change', function() {

            document.querySelectorAll('.package-item').forEach(updatePaketOptions);

        });

        document.getElementById('biaya_tambahan').addEventListener('input', hitungTotal);
        document.getElementById('diskon').addEventListener('input', hitungTotal);


        resetBtn.addEventListener('click', function() {

            paketContainer.innerHTML = '';
            addPaketItem();
            hitungTotal();
            togglePelangganFields();

        });


        form.addEventListener('submit', function(e) {

            const packageItems = document.querySelectorAll('.package-item');

            if (packageItems.length === 0) {

                e.preventDefault();
                alert('Harus menambahkan minimal 1 paket laundry.');

                return;

            }

            let valid = true;

            document.querySelectorAll('.paket-select').forEach(select => {

                if (!select.value) {

                    valid = false;
                    select.focus();

                }

            });

            if (!valid) {

                e.preventDefault();
                alert('Semua paket harus dipilih.');

                return;

            }

            const tipePelanggan = tipePelangganSelect.value;

            if (tipePelanggan === 'member' && !memberSelect.value) {

                e.preventDefault();
                alert('Harus memilih member untuk tipe pelanggan member.');
                memberSelect.focus();

                return;

            }

            if (tipePelanggan === 'biasa' && !namaPelangganInput.value.trim()) {

                e.preventDefault();
                alert('Harus mengisi nama pelanggan untuk tipe pelanggan biasa.');
                namaPelangganInput.focus();

                return;

            }


            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...';
            submitBtn.disabled = true;


            const numericFields = ['biaya_tambahan', 'pajak'];

            numericFields.forEach(field => {

                const input = document.querySelector(`[name="${field}"]`);

                if (input && (!input.value || input.value.trim() === '')) {

                    input.value = '0';
                }

            });

        });


        togglePelangganFields();
        addPaketItem();


        document.getElementById('biaya_tambahan').value = '0';
        document.getElementById('diskon').value = '0';
        document.getElementById('pajak').value = '1000';

        hitungTotal();

    });

    </script>
</body>
</html>
