<!DOCTYPE html>
<html>
<head>
    <title>Outlet List - Backend Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Outlet Management - Backend Test</h1>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="mb-3">
            <a href="{{ route('admin.outlets.create') }}" class="btn btn-primary">Tambah Outlet</a>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Kembali ke Dashboard</a>
        </div>

        @if($outlets->count() > 0)
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Alamat</th>
                        <th>Telepon</th>
                        <th>Jumlah User</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($outlets as $outlet)
                    <tr>
                        <td>{{ $outlet->id }}</td>
                        <td>{{ $outlet->nama }}</td>
                        <td>{{ $outlet->alamat }}</td>
                        <td>{{ $outlet->tlp }}</td>
                        <td>{{ $outlet->users_count }}</td>
                        <td>{{ $outlet->created_at->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route('admin.outlets.show', $outlet) }}" class="btn btn-sm btn-info">View</a>
                            <a href="{{ route('admin.outlets.edit', $outlet) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('admin.outlets.destroy', $outlet) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Hapus outlet {{ $outlet->nama }}?')"
                                    {{ $outlet->users_count > 0 ? 'disabled' : '' }}>
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="alert alert-info">Tidak ada data outlet</div>
        @endif
    </div>
</body>
</html>
