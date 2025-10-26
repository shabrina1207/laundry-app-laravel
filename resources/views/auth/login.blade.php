<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Twinkle Wash</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .login-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background: #0056b3;
        }

        .error {
            color: red;
            font-size: 14px;
            margin-top: 5px;
        }

        .success {
            color: green;
            font-size: 14px;
            margin-bottom: 15px;
            padding: 10px;
            background: #d4edda;
            border-radius: 4px;
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
        }

        .login-info {
            background: #e7f3ff;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-size: 12px;
            text-align: center;
        }

        .forgot-password-link {
            text-align: center;
            margin: 15px 0;
        }

        .forgot-password-link a {
            color: #007bff;
            text-decoration: none;
        }

        .forgot-password-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <h1>Login Twinkle Wash</h1>

        @if (session('success'))
            <div class="success">{{ session('success') }}</div>
        @endif

        @if (session('status'))
            <div class="success">{{ session('status') }}</div>
        @endif

        @if (session('message'))
            <div class="success">{{ session('message') }}</div>
        @endif

        <div class="login-info">
            <strong>Info Login:</strong> Gunakan email atau username Anda
        </div>

        <form method="POST" action="{{ url('/login') }}">
            @csrf

            <div class="form-group">
                <label>Email atau Username</label>
                <input type="text" name="email" value="{{ old('email') }}" required autofocus>
                @error('email')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
                @error('password')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit">Login</button>
        </form>

        <div class="forgot-password-link">
            <a href="{{ route('password.request') }}">Lupa Password?</a>
        </div>

        <div class="register-link">
            <p>Belum punya akun? <a href="{{ url('/register') }}">Daftar di sini</a></p>
        </div>

        @if ($errors->any())
            <div class="error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</body>

</html>
