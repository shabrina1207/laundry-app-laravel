<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email - Laundry App</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 500px; margin: 100px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .btn { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block; border: none; cursor: pointer; }
        .success { color: #28a745; }
        .info { color: #17a2b8; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Verifikasi Email Anda</h1>
        
        @if(session('message'))
            <div class="success">{{ session('message') }}</div>
        @endif

        <p>Sebelum melanjutkan, silakan verifikasi email Anda dengan mengklik link yang kami kirim ke email Anda.</p>
        
        <p>Jika Anda tidak menerima email:</p>
        
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn">Kirim Ulang Link Verifikasi</button>
        </form>

        <div style="margin-top: 20px;">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn" style="background: #6c757d;">Kembali ke Login</button>
            </form>
        </div>
    </div>
</body>
</html>