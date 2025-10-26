<!DOCTYPE html>
<html>
<head>
    <title>Paket List - Backend Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Paket Management - Backend Test</h1>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="mb-3">
            <a href="{{ route(auth()->user()->role . '.paket.create') }}" class="btn btn-primary">Tambah Paket</a>
            <a href="{{ route(auth()->user()->role . '.dashboard') }}" class="btn btn-secondary">Kembali ke Dashboard</a>
        </div>

        @if($pakets->count() > 0)
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Outlet</th>
                        <th>Jenis</th>
                        <th>Nama Paket</th>
                        <th>Harga</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pakets as $paket)
                    <tr>
                        <td>{{ $paket->id }}</td>
                        <td>{{ $paket->outlet->nama }}</td>
                        <td>
                            <span class="badge
                                @if($paket->jenis == 'kiloan') bg-primary
                                @elseif($paket->jenis == 'selimut') bg-success
                                @elseif($paket->jenis == 'bed_cover') bg-warning
                                @elseif($paket->jenis == 'kaos') bg-info
                                @else bg-secondary
                                @endif">
                                {{ $paket->jenis_formatted }}
                            </span>
                        </td>
                        <td>{{ $paket->nama_paket }}</td>
                        <td>{{ $paket->harga_formatted }}</td>
                        <td>
                            <a href="{{ route(auth()->user()->role . '.paket.show', $paket) }}" class="btn btn-sm btn-info">View</a>
                            <a href="{{ route(auth()->user()->role . '.paket.edit', $paket) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route(auth()->user()->role . '.paket.destroy', $paket) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Hapus paket {{ $paket->nama_paket }}?')">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="alert alert-info">Tidak ada data paket</div>
        @endif
    </div>
</body>
</html>
