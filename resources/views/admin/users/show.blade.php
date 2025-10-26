<!DOCTYPE html>
<html>
<head>
    <title>Pengguna Detail - Backend Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Detail Pengguna: {{ $user->name }}</h1>

        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary mb-3">← Kembali ke List</a>

        <div class="card">
            <div class="card-body">
                <table class="table">
                    <tr>
                        <th width="30%">ID Pengguna</th>
                        <td>{{ $user->id }}</td>
                    </tr>
                    <tr>
                        <th>Nama Lengkap</th>
                        <td>{{ $user->name }}</td>
                    </tr>
                    <tr>
                        <th>Username</th>
                        <td>{{ $user->username }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <th>Role</th>
                        <td>
                            <span class="badge
                                @if($user->role == 'admin') bg-danger
                                @elseif($user->role == 'owner') bg-warning
                                @elseif($user->role == 'kasir') bg-info
                                @else bg-secondary
                                @endif">
                                {{ $user->role_formatted }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Outlet</th>
                        <td>{{ $user->nama_outlet }}</td>
                    </tr>
                    <tr>
                        <th>Telepon</th>
                        <td>{{ $user->phone ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Alamat</th>
                        <td>{{ $user->address ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Tanggal Dibuat</th>
                        <td>{{ $user->created_at->format('d M Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Terakhir Diupdate</th>
                        <td>{{ $user->updated_at->format('d M Y H:i') }}</td>
                    </tr>
                </table>
            </div>
            <div class="card-footer">
                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-warning">Edit</a>
                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger"
                        onclick="return confirm('Hapus pengguna {{ $user->name }}?')">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
