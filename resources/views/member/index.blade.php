<!DOCTYPE html>
<html>
<head>
    <title>Data Pelanggan - Backend Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Data Pelanggan</h1>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="mb-3">
            <a href="{{
                auth()->user()->role === 'admin' ?
                route('admin.members.create') :
                route('kasir.members.create')
            }}" class="btn btn-primary">Tambah Pelanggan</a>

            @if(auth()->user()->role === 'admin')
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Kembali ke Dashboard</a>
            @else
                <a href="{{ route('kasir.dashboard') }}" class="btn btn-secondary">Kembali ke Dashboard</a>
            @endif
        </div>

        @if($members->count() > 0)
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Telepon</th>
                        <th>Jenis Kelamin</th>
                        <th>Alamat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($members as $member)
                    <tr>
                        <td>{{ $member->id }}</td>
                        <td>{{ $member->nama }}</td>
                        <td>{{ $member->tlp }}</td>
                        <td>
                            @if($member->jenis_kelamin == 'L')
                                Laki-laki
                            @elseif($member->jenis_kelamin == 'P')
                                Perempuan
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $member->alamat ?? '-' }}</td>
                        <td>
                            @if(auth()->user()->role === 'admin')
                            <a href="{{ route('admin.members.edit', $member->id) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('admin.members.destroy', $member->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Hapus pelanggan {{ $member->nama }}?')">
                                    Hapus
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="alert alert-info">Tidak ada data pelanggan</div>
        @endif
    </div>
</body>
</html>
