<!DOCTYPE html>
<html>
<head>
    <title>Paket Detail - Backend Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Detail Paket: {{ $paket->nama_paket }}</h1>

        <a href="{{ route(auth()->user()->role . '.paket.index') }}" class="btn btn-secondary mb-3">← Kembali ke List</a>

        <div class="card">
            <div class="card-body">
                <table class="table">
                    <tr>
                        <th width="30%">ID Paket</th>
                        <td>{{ $paket->id }}</td>
                    </tr>
                    <tr>
                        <th>Outlet</th>
                        <td>{{ $paket->outlet->nama }}</td>
                    </tr>
                    <tr>
                        <th>Jenis Paket</th>
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
                    </tr>
                    <tr>
                        <th>Nama Paket</th>
                        <td>{{ $paket->nama_paket }}</td>
                    </tr>
                    <tr>
                        <th>Harga</th>
                        <td>{{ $paket->harga_formatted }}</td>
                    </tr>
                </table>
            </div>
            <div class="card-footer">
                <a href="{{ route(auth()->user()->role . '.paket.edit', $paket) }}" class="btn btn-warning">Edit</a>
                <form action="{{ route(auth()->user()->role . '.paket.destroy', $paket) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger"
                        onclick="return confirm('Hapus paket {{ $paket->nama_paket }}?')">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
