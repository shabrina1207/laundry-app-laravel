<!DOCTYPE html>
<html>
<head>
    <title>Daftar Transaksi - Twinkle Wash</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .status-dropdown {
            min-width: 140px;
        }
        .payment-dropdown {
            min-width: 160px;
        }
        .badge-clickable {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .badge-clickable:hover {
            opacity: 0.8;
            transform: scale(1.05);
        }
        .loading-spinner {
            display: none;
            width: 16px;
            height: 16px;
        }
        .toast-container {
            z-index: 9999;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .status-badge, .payment-badge {
            font-size: 0.8em;
            padding: 0.4em 0.6em;
        }
        .table > :not(caption) > * > * {
            padding: 0.75rem 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0"><i class="fas fa-list me-2"></i>Daftar Transaksi</h1>
            <div>
                <a href="{{ route('transaksi.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Transaksi Baru
                </a>
                <a href="{{ route(auth()->user()->role . '.dashboard') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($transaksi->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Kode Invoice</th>
                            <th>Pelanggan</th>
                            <th>Tipe</th>
                            <th>Outlet</th>
                            <th>Tanggal</th>
                            <th>Batas Waktu</th>
                            <th>Status</th>
                            <th>Pembayaran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transaksi as $item)
                        <tr class="@if($item->is_terlambat) table-danger @endif">
                            <td>
                                <strong>{{ $item->kode_invoice }}</strong>
                                @if($item->is_terlambat)
                                    <span class="badge bg-danger ms-1">Terlambat</span>
                                @endif
                            </td>
                            <td>
                                @if($item->tipe_pelanggan === 'member')
                                    {{ $item->member->nama ?? 'N/A' }}
                                    <span class="badge bg-info ms-1">Member</span>
                                @else
                                    {{ $item->nama_pelanggan }}
                                    <span class="badge bg-secondary ms-1">Biasa</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge
                                    @if($item->tipe_pelanggan === 'member') bg-info
                                    @else bg-secondary @endif">
                                    {{ $item->tipe_pelanggan_formatted }}
                                </span>
                            </td>
                            <td>{{ $item->outlet->nama }}</td>
                            <td>{{ $item->tgl->format('d/m/Y H:i') }}</td>
                            <td class="@if($item->is_terlambat) text-danger fw-bold @endif">
                                {{ $item->batas_waktu->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                <div class="dropdown d-inline-block">
                                    <span class="badge badge-clickable status-badge
                                        @if($item->status == 'baru') bg-secondary
                                        @elseif($item->status == 'proses') bg-warning text-dark
                                        @elseif($item->status == 'selesai') bg-info
                                        @elseif($item->status == 'diambil') bg-success @endif"
                                        data-bs-toggle="dropdown"
                                        data-transaksi-id="{{ $item->id }}"
                                        data-field="status"
                                        data-current-value="{{ $item->status }}">
                                        {{ $item->status_formatted }}
                                        <i class="fas fa-caret-down ms-1"></i>
                                    </span>
                                    <ul class="dropdown-menu status-dropdown">
                                        <li><a class="dropdown-item status-option" href="#" data-value="baru">
                                            <span class="badge bg-secondary me-2">Baru</span>
                                            Status awal transaksi
                                        </a></li>
                                        <li><a class="dropdown-item status-option" href="#" data-value="proses">
                                            <span class="badge bg-warning text-dark me-2">Diproses</span>
                                            Sedang dalam pengerjaan
                                        </a></li>
                                        <li><a class="dropdown-item status-option" href="#" data-value="selesai">
                                            <span class="badge bg-info me-2">Selesai</span>
                                            Pengerjaan selesai
                                        </a></li>
                                        <li><a class="dropdown-item status-option" href="#" data-value="diambil">
                                            <span class="badge bg-success me-2">Diambil</span>
                                            Sudah diambil pelanggan
                                        </a></li>
                                    </ul>
                                    <div class="loading-spinner spinner-border spinner-border-sm text-primary ms-1"></div>
                                </div>
                            </td>
                            <td>
                                <div class="dropdown d-inline-block">
                                    <span class="badge badge-clickable payment-badge
                                        @if($item->dibayar == 'dibayar') bg-success
                                        @else bg-danger @endif"
                                        data-bs-toggle="dropdown"
                                        data-transaksi-id="{{ $item->id }}"
                                        data-field="dibayar"
                                        data-current-value="{{ $item->dibayar }}">
                                        {{ $item->dibayar_formatted }}
                                        <i class="fas fa-caret-down ms-1"></i>
                                    </span>
                                    <ul class="dropdown-menu payment-dropdown">
                                        <li><a class="dropdown-item payment-option" href="#" data-value="dibayar">
                                            <span class="badge bg-success me-2">Sudah Dibayar</span>
                                            Pelanggan sudah melunasi
                                        </a></li>
                                        <li><a class="dropdown-item payment-option" href="#" data-value="belum_dibayar">
                                            <span class="badge bg-danger me-2">Belum Dibayar</span>
                                            Menunggu pembayaran
                                        </a></li>
                                    </ul>
                                    <div class="loading-spinner spinner-border spinner-border-sm text-primary ms-1"></div>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('transaksi.show', $item->id) }}"
                                       class="btn btn-info" title="Detail Transaksi">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('transaksi.print', $item->id) }}"
                                       class="btn btn-secondary" title="Print Invoice" target="_blank">
                                        <i class="fas fa-print"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle me-2"></i> Tidak ada data transaksi
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>

    document.addEventListener('DOMContentLoaded', function() {

        function updateStatus(transaksiId, field, value, badgeElement) {
            
            const spinner = badgeElement.parentElement.querySelector('.loading-spinner');
            const originalBadge = badgeElement.cloneNode(true);


            spinner.style.display = 'inline-block';
            badgeElement.style.opacity = '0.5';


            fetch(`/transaksi/${transaksiId}/update-status-quick`, {

                method: 'POST',
                headers: {

                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },

                body: JSON.stringify({

                    field: field,
                    value: value

                })

            })

            .then(response => {

                if (!response.ok) {

                    throw new Error('Network response was not ok');

                }

                return response.json();

            })

            .then(data => {

                if (data.success) {

                    if (field === 'status') {

                        updateStatusBadge(badgeElement, value);

                    } else if (field === 'dibayar') {

                        updatePaymentBadge(badgeElement, value);

                    }


                    showToast('Success', data.message, 'success');

                } else {

                    throw new Error(data.message || 'Terjadi kesalahan');

                }

            })

            .catch(error => {

                console.error('Error:', error);
                showToast('Error', 'Gagal mengupdate status: ' + error.message, 'error');

                badgeElement.replaceWith(originalBadge);

            })

            .finally(() => {

                spinner.style.display = 'none';
                badgeElement.style.opacity = '1';

            });

        }


        function updateStatusBadge(badgeElement, value) {

            const statusMap = {

                'baru': { class: 'bg-secondary', text: 'Baru' },
                'proses': { class: 'bg-warning text-dark', text: 'Diproses' },
                'selesai': { class: 'bg-info', text: 'Selesai' },
                'diambil': { class: 'bg-success', text: 'Diambil' }

            };

            const status = statusMap[value];

            if (status) {

                badgeElement.className = `badge badge-clickable status-badge ${status.class}`;
                badgeElement.innerHTML = status.text + ' <i class="fas fa-caret-down ms-1"></i>';
                badgeElement.setAttribute('data-current-value', value);

            }

        }


        function updatePaymentBadge(badgeElement, value) {

            const paymentMap = {

                'dibayar': { class: 'bg-success', text: 'Sudah Dibayar' },
                'belum_dibayar': { class: 'bg-danger', text: 'Belum Dibayar' }

            };

            const payment = paymentMap[value];

            if (payment) {

                badgeElement.className = `badge badge-clickable payment-badge ${payment.class}`;
                badgeElement.innerHTML = payment.text + ' <i class="fas fa-caret-down ms-1"></i>';
                badgeElement.setAttribute('data-current-value', value);

            }

        }


        function showToast(title, message, type) {

            const toastId = 'toast-' + Date.now();
            const toast = document.createElement('div');
            toast.id = toastId;
            toast.className = `toast align-items-center text-bg-${type} border-0`;
            toast.setAttribute('role', 'alert');
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'} me-2"></i>
                        <strong>${title}:</strong> ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;


            let toastContainer = document.querySelector('.toast-container');

            if (!toastContainer) {

                toastContainer = document.createElement('div');
                toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
                document.body.appendChild(toastContainer);

            }

            toastContainer.appendChild(toast);


            const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
            bsToast.show();


            toast.addEventListener('hidden.bs.toast', () => {

                toast.remove();

            });

        }


        document.querySelectorAll('.status-option').forEach(option => {

            option.addEventListener('click', function(e) {

                e.preventDefault();
                const dropdown = this.closest('.dropdown');
                const badge = dropdown.querySelector('.badge-clickable');
                const transaksiId = badge.getAttribute('data-transaksi-id');
                const value = this.getAttribute('data-value');
                const currentValue = badge.getAttribute('data-current-value');

                if (value !== currentValue) {

                    updateStatus(transaksiId, 'status', value, badge);

                }


                const bsDropdown = bootstrap.Dropdown.getInstance(badge);

                if (bsDropdown) {

                    bsDropdown.hide();

                }

            });

        });


        document.querySelectorAll('.payment-option').forEach(option => {

            option.addEventListener('click', function(e) {

                e.preventDefault();
                const dropdown = this.closest('.dropdown');
                const badge = dropdown.querySelector('.badge-clickable');
                const transaksiId = badge.getAttribute('data-transaksi-id');
                const value = this.getAttribute('data-value');
                const currentValue = badge.getAttribute('data-current-value');

                if (value !== currentValue) {

                    updateStatus(transaksiId, 'dibayar', value, badge);

                }


                const bsDropdown = bootstrap.Dropdown.getInstance(badge);

                if (bsDropdown) {

                    bsDropdown.hide();

                }

            });

        });


        document.querySelectorAll('.badge-clickable').forEach(badge => {

            badge.addEventListener('click', function(e) {

                e.stopPropagation();

            });

        });


        document.querySelectorAll('.badge-clickable').forEach(badge => {

            new bootstrap.Dropdown(badge);

        });

    });

    </script>
</body>
</html>
