<!DOCTYPE html>
<html>
<head>
    <title>Outlet Detail - Backend Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Detail Outlet: {{ $outlet->nama }}</h1>

        <a href="{{ route('admin.outlets.index') }}" class="btn btn-secondary mb-3">← Kembali ke List</a>

        <div class="card">
            <div class="card-body">
                <table class="table">
                    <tr>
                        <th width="30%">ID Outlet</th>
                        <td>{{ $outlet->id }}</td>
                    </tr>
                    <tr>
                        <th>Nama Outlet</th>
                        <td>{{ $outlet->nama }}</td>
                    </tr>
                    <tr>
                        <th>Telepon</th>
                        <td>{{ $outlet->tlp }}</td>
                    </tr>
                    <tr>
                        <th>Jumlah User</th>
                        <td>{{ $outlet->users_count }}</td>
                    </tr>
                    <tr>
                        <th>Total Transaksi</th>
                        <td>{{ $transaksiCount }}</td>
                    </tr>
                    <tr>
                        <th>Dibuat Pada</th>
                        <td>{{ $outlet->created_at->format('d M Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Diupdate Pada</th>
                        <td>{{ $outlet->updated_at->format('d M Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Alamat</th>
                        <td>{{ $outlet->alamat }}</td>
                    </tr>
                </table>
            </div>
            <div class="card-footer">
                <a href="{{ route('admin.outlets.edit', $outlet) }}" class="btn btn-warning">Edit</a>
                <form action="{{ route('admin.outlets.destroy', $outlet) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger"
                        onclick="return confirm('Hapus outlet {{ $outlet->nama }}?')"
                        {{ $outlet->users_count > 0 ? 'disabled' : '' }}>
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
