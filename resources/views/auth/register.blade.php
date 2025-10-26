<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Twinkle Wash</title>
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
        .register-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        textarea {
            height: 80px;
            resize: vertical;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #218838;
        }
        .error {
            color: red;
            font-size: 14px;
            margin-top: 5px;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .test-accounts {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 12px;
        }
        .role-warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 8px;
            border-radius: 4px;
            margin-top: 8px;
            font-size: 11px;
        }
        .password-requirements {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            font-size: 12px;
            margin-top: 5px;
            border-left: 4px solid #007bff;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h1>Registrasi - Twinkle Wash</h1>

        <form method="POST" action="{{ url('/register') }}" id="registerForm">
            @csrf

            <div class="form-group">
                <label>Nama Lengkap *</label>
                <input type="text" name="name" value="{{ old('name') }}"
                       required
                       maxlength="255">
                @error('name')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label>Username *</label>
                <input type="text" name="username" value="{{ old('username') }}"
                       required
                       maxlength="8"
                       minlength="3"
                       pattern="[a-zA-Z0-9_]+"
                       title="Username harus 3-8 karakter, hanya boleh huruf, angka, dan underscore">
                <small style="color: #666; font-size: 12px;">Username harus 3-8 karakter (huruf, angka, underscore)</small>
                @error('username')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       required>
                @error('email')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label>Daftar Sebagai *</label>
                <select name="role" required>
                    <option value="">Pilih Role</option>
                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrator</option>
                    <option value="owner" {{ old('role') == 'owner' ? 'selected' : '' }}>Owner Laundry</option>
                    <option value="kasir" {{ old('role') == 'kasir' ? 'selected' : '' }}>Staff Kasir</option>
                </select>
                @error('role')
                    <div class="error">{{ $message }}</div>
                @enderror

                <div class="role-warning">
                    <strong>Perhatian:</strong> Role Administrator memiliki akses penuh ke sistem.
                </div>
            </div>

            <div class="form-group">
                <label>Nomor Telepon *</label>
                <input type="text" name="phone" value="{{ old('phone') }}"
                       required
                       maxlength="15"
                       pattern="[0-9]+"
                       title="Hanya angka yang diperbolehkan">
                <small style="color: #666; font-size: 12px;">Hanya angka, maksimal 15 digit</small>
                @error('phone')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label>Alamat *</label>
                <textarea name="address" required maxlength="500">{{ old('address') }}</textarea>
                <small style="color: #666; font-size: 12px;">Maksimal 500 karakter</small>
                @error('address')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label>Password *</label>
                <input type="password" name="password" id="password"
                       required
                       minlength="8"
                       maxlength="20"
                       pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$"
                       title="Password harus mengandung huruf besar, huruf kecil, dan angka">
                <div class="password-requirements">
                    <strong>Password harus mengandung:</strong><br>
                    • Minimal 8 karakter, maksimal 20 karakter<br>
                    • Minimal 1 huruf kecil (a-z)<br>
                    • Minimal 1 huruf besar (A-Z)<br>
                    • Minimal 1 angka (0-9)
                </div>
                @error('password')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label>Konfirmasi Password *</label>
                <input type="password" name="password_confirmation"
                       required
                       minlength="8"
                       maxlength="20">
                @error('password_confirmation')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit">Daftar</button>
        </form>

        <div class="login-link">
            <p>Sudah punya akun? <a href="{{ url('/login') }}">Login di sini</a></p>
        </div>

        
        <div class="test-accounts">
            <strong>Test Accounts (Untuk Development):</strong><br>
            • Email: admin@laundry.com atau Username: admin / Password123<br>
            • Email: kasir@laundry.com atau Username: kasir / Password123<br>
            • Email: owner@laundry.com atau Username: owner / Password123<br>
            <em>Atau register baru dengan role pilihan Anda</em>
        </div>

        @if($errors->any())
            <div class="error">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const passwordInput = document.getElementById('password');
        const form = document.getElementById('registerForm');


        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const hasLowerCase = /[a-z]/.test(password);
            const hasUpperCase = /[A-Z]/.test(password);
            const hasNumber = /\d/.test(password);
            const hasValidLength = password.length >= 8 && password.length <= 20;

            if (password.length > 0) {
                const requirements = document.querySelector('.password-requirements');
                if (hasLowerCase && hasUpperCase && hasNumber && hasValidLength) {
                    requirements.style.borderLeftColor = '#28a745';
                    requirements.style.background = '#d4edda';
                } else {
                    requirements.style.borderLeftColor = '#dc3545';
                    requirements.style.background = '#f8d7da';
                }
            }
        });


        form.addEventListener('submit', function(e) {
            const password = passwordInput.value;
            const hasLowerCase = /[a-z]/.test(password);
            const hasUpperCase = /[A-Z]/.test(password);
            const hasNumber = /\d/.test(password);
            const hasValidLength = password.length >= 8 && password.length <= 20;

            if (!hasLowerCase || !hasUpperCase || !hasNumber || !hasValidLength) {
                e.preventDefault();
                alert('Password harus memenuhi semua persyaratan: minimal 8 karakter, mengandung huruf besar, huruf kecil, dan angka.');
            }
        });
    });
    </script>
</body>
</html>
