<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use App\Helpers\FormatHelper;

class AuthController extends Controller {

    public function showLoginForm() {

        return view('auth.login');

    }

    public function showRegisterForm() {

        return view('auth.register');

    }

    public function showForgotPasswordForm() {

        return view('auth.forgot-password');

    }

    public function sendResetLinkEmail(Request $request) {

        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(

            $request->only('email')

        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);

    }

    public function showResetPasswordForm(Request $request, $token = null) {

        return view('auth.reset-password', [

            'token' => $token,
            'email' => $request->email

        ]);

    }

    public function resetPassword(Request $request) {

        $request->validate([

            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|max:20|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',

        ], [

            'password.regex' => 'Password harus mengandung huruf besar, huruf kecil, dan angka.',
            'password.min' => 'Password harus minimal 8 karakter.',
            'password.max' => 'Password tidak boleh lebih dari 20 karakter.'

        ]);

        $status = Password::reset(

            $request->only('email', 'password', 'password_confirmation', 'token'),

            function ($user, $password) {

                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));

            }

        );

        return $status == Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);

    }

    public function register(Request $request) {

        $validator = Validator::make($request->all(), [

            'name' => 'required|string|max:255',
            'username' => 'required|string|max:8|min:3|unique:users|regex:/^[a-zA-Z0-9_]+$/',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|max:20|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            'phone' => 'required|string|max:15|regex:/^[0-9]+$/',
            'address' => 'required|string|max:500',
            'role' => 'required|in:admin,owner,kasir'

        ], [

            'username.max' => 'Username tidak boleh lebih dari 8 karakter.',
            'username.min' => 'Username harus minimal 3 karakter.',
            'username.regex' => 'Username hanya boleh mengandung huruf, angka, dan underscore.',
            'password.min' => 'Password harus minimal 8 karakter.',
            'password.max' => 'Password tidak boleh lebih dari 20 karakter.',
            'password.regex' => 'Password harus mengandung huruf besar, huruf kecil, dan angka.',
            'phone.regex' => 'Nomor telepon hanya boleh mengandung angka.',
            'address.max' => 'Alamat tidak boleh lebih dari 500 karakter.'

        ]);

        if ($validator->fails()) {

            return redirect()->back()->withErrors($validator)->withInput();

        }

        $existingUser = User::where('email', $request->email)->first();

        if ($existingUser) {

            return redirect()->back()->withErrors(['email' => 'Email sudah terdaftar.'])->withInput();

        }

        $encryptedPhone = Crypt::encryptString($request->phone);
        $encryptedAddress = Crypt::encryptString($request->address);

        $id_outlet = null;

        if (in_array($request->role, ['admin', 'owner', 'kasir'])) {

            $outlet = Outlet::first();
            $id_outlet = $outlet ? $outlet->id : 1;

        }

        $user = User::create([

            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $encryptedPhone,
            'address' => $encryptedAddress,
            'role' => $request->role,
            'id_outlet' => $id_outlet

        ]);

        $user->markEmailAsVerified();

        return redirect('/login')->with('success', 'Registrasi berhasil! Silakan login.');
    }

    public function login(Request $request) {

        $credentials = $request->validate([

            'email' => 'required|string',
            'password' => 'required'

        ]);

        $field = filter_var($credentials['email'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $loginCredentials = [

            $field => $credentials['email'],
            'password' => $credentials['password']

        ];

        if (Auth::attempt($loginCredentials)) {

            $user = Auth::user();

            $request->session()->regenerate();

            return $this->redirectToDashboard($user->role);

        }

        return back()->withErrors([

            'email' => 'Email/username atau password salah.',

        ])->withInput();

    }

    private function redirectToDashboard($role) {

        switch ($role) {

            case 'admin':
                return redirect()->route('admin.dashboard')->with('success', 'Login berhasil! Selamat datang Admin.');
            case 'kasir':
                return redirect()->route('kasir.dashboard')->with('success', 'Login berhasil! Selamat datang Kasir.');
            case 'owner':
                return redirect()->route('owner.dashboard')->with('success', 'Login berhasil! Selamat datang Owner.');
            default:
                return redirect('/dashboard')->with('success', 'Login berhasil!');

        }

    }

    public function logout(Request $request) {

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Logout berhasil!');

    }

    public function showProfile() {
        $user = Auth::user();
        return view('profile.index', compact('user'));

    }

    public function updateProfile(Request $request) {

        $user = Auth::user();

        $validator = Validator::make($request->all(), [

            'name' => 'required|string|max:255',
            'username' => 'required|string|max:8|min:3|regex:/^[a-zA-Z0-9_]+$/|unique:users,username,' . $user->id,
            'phone' => 'required|string|max:15|regex:/^[0-9]+$/',
            'address' => 'required|string|max:500'

        ], [

            'username.max' => 'Username tidak boleh lebih dari 8 karakter.',
            'username.min' => 'Username harus minimal 3 karakter.',
            'username.regex' => 'Username hanya boleh mengandung huruf, angka, dan underscore.',
            'phone.regex' => 'Nomor telepon hanya boleh mengandung angka.',
            'address.max' => 'Alamat tidak boleh lebih dari 500 karakter.'

        ]);

        if ($validator->fails()) {

            return redirect()->back()->withErrors($validator)->withInput();

        }

        $user->update([

            'name' => $request->name,
            'username' => $request->username,
            'phone' => $request->phone,
            'address' => $request->address

        ]);

        return redirect()->back()->with('success', 'Profile berhasil diperbarui!');

    }

    public function changePassword(Request $request) {

        $validator = Validator::make($request->all(), [

            'current_password' => 'required',
            'new_password' => 'required|min:8|max:20|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',

        ], [

            'new_password.regex' => 'Password harus mengandung huruf besar, huruf kecil, dan angka.',
            'new_password.min' => 'Password harus minimal 8 karakter.',
            'new_password.max' => 'Password tidak boleh lebih dari 20 karakter.'

        ]);

        if ($validator->fails()) {

            return redirect()->back()->withErrors($validator)->withInput();

        }

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {

            return redirect()->back()->withErrors(['current_password' => 'Password saat ini salah.'])->withInput();

        }

        $user->update([

            'password' => Hash::make($request->new_password)

        ]);

        return redirect()->back()->with('success', 'Password berhasil diubah!');

    }

    public function resendVerification(Request $request) {

        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {

            return redirect()->route('dashboard')->with('info', 'Email sudah terverifikasi.');

        }

        $user->sendEmailVerificationNotification();

        return back()->with('success', 'Link verifikasi telah dikirim ke email Anda!');

    }

}
