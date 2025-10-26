<!DOCTYPE html>
<html>
<head>
    <title>Edit Outlet - Backend Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Edit Outlet: {{ $outlet->nama }}</h1>

        <a href="{{ route('admin.outlets.index') }}" class="btn btn-secondary mb-3">← Kembali</a>

        <form action="{{ route('admin.outlets.update', $outlet) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label">Nama Outlet *</label>
                <input type="text" name="nama" class="form-control" value="{{ old('nama', $outlet->nama) }}" required>
                @error('nama') <div class="text-danger">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Alamat *</label>
                <textarea name="alamat" class="form-control" rows="3" required>{{ old('alamat', $outlet->alamat) }}</textarea>
                @error('alamat') <div class="text-danger">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Telepon *</label>
                <input type="text" name="tlp" class="form-control" value="{{ old('tlp', $outlet->tlp) }}" required>
                @error('tlp') <div class="text-danger">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('admin.outlets.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</body>
</html>
