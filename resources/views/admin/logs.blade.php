<!DOCTYPE html>
<html>
<head>
    <title>System Logs - Backend Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>System Logs</h1>

        
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif


        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif


        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">← Kembali ke Dashboard</a>
            <div>
                <form action="{{ route('admin.logs.clear') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger"
                            onclick="return confirm('Yakin hapus semua logs?')">
                        Hapus Semua Logs
                    </button>
                </form>
            </div>
        </div>


        <div class="card mb-3">
            <div class="card-body">
                <form action="{{ route('admin.logs.search') }}" method="GET">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control"
                               placeholder="Cari aktivitas atau user..."
                               value="{{ request('search') }}">
                        <button type="submit" class="btn btn-primary">Cari</button>
                    </div>
                </form>
            </div>
        </div>


        @if($logs->count() > 0)
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Data Logs (Total: {{ $logs->total() }})</h5>
                    <span class="badge bg-primary">{{ $logs->count() }} data ditampilkan</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>Aktivitas</th>
                                    <th>Tanggal</th>
                                    <th>Data Terkait</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $log)
                                <tr>
                                    <td>{{ $log->id_log }}</td>
                                    <td>
                                        @if($log->user)
                                            <strong>{{ $log->user->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $log->user->email }}</small>
                                        @else
                                            <span class="text-muted">System</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $log->aktivitas }}</strong>
                                    </td>
                                    <td>
                                        <small>{{ $log->tanggal->format('d M Y H:i') }}</small>
                                        <br>
                                        <small class="text-muted">{{ $log->tanggal->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        @if($log->data_terkait && is_array($log->data_terkait))
                                            <button type="button" class="btn btn-sm btn-outline-info"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#detailModal{{ $log->id_log }}">
                                                Lihat Detail
                                            </button>


                                            <div class="modal fade" id="detailModal{{ $log->id_log }}" tabindex="-1">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Detail Data - Log #{{ $log->id_log }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <pre>{{ json_encode($log->data_terkait, JSON_PRETTY_PRINT) }}</pre>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif($log->data_terkait && is_string($log->data_terkait))
                                            <small class="text-muted">{{ Str::limit($log->data_terkait, 50) }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>


                    <div class="d-flex justify-content-center mt-3">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-info text-center">
                <h4>Tidak ada data logs</h4>
                <p>Belum ada aktivitas yang tercatat dalam sistem.</p>
            </div>
        @endif
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
