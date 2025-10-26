<!DOCTYPE html>
<html>
<head>
    <title>Registrasi Pelanggan - Backend Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Registrasi Pelanggan Baru</h1>

        <a href="{{
            auth()->user()->role === 'admin' ?
            route('admin.members.index') :
            route('kasir.members.index')
        }}" class="btn btn-secondary mb-3">← Kembali</a>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{
            auth()->user()->role === 'admin' ?
            route('admin.members.store') :
            route('kasir.members.store')
        }}">
            @csrf

            <div class="mb-3">
                <label for="nama" class="form-label">Nama *</label>
                <input type="text" class="form-control" id="nama" name="nama" value="{{ old('nama') }}" required>
                @error('nama') <div class="text-danger">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label for="alamat" class="form-label">Alamat</label>
                <textarea class="form-control" id="alamat" name="alamat" rows="3">{{ old('alamat') }}</textarea>
                @error('alamat') <div class="text-danger">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                <select class="form-control" id="jenis_kelamin" name="jenis_kelamin">
                    <option value="">Pilih</option>
                    <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                </select>
                @error('jenis_kelamin') <div class="text-danger">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label for="tlp" class="form-label">Telepon</label>
                <input type="text" class="form-control" id="tlp" name="tlp" value="{{ old('tlp') }}">
                @error('tlp') <div class="text-danger">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{
                auth()->user()->role === 'admin' ?
                route('admin.members.index') :
                route('kasir.members.index')
            }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</body>
</html>
