<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use App\Models\User;

class AuthController extends Controller
{
    const SESSION_TIMEOUT = 3600;

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate(
            [
                'username' => 'required|string',
                'password' => 'required',
                'captcha'  => 'required|captcha',
            ],
            [
                'captcha.captcha' => 'Captcha salah!',
                'captcha.required' => 'Captcha wajib diisi!',
            ]
        );

        $user = User::with('opd')->where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors(['username' => 'Username atau password salah.'])->withInput();
        }
        if ($user->needsPasswordChange()) {
            session([
                'user_id' => $user->id,
                'user' => $user,
                'must_change_password' => true,
                'last_activity' => time()
            ]);

            return redirect('/dashboard/change-password')
                ->with('warning', 'Password Anda sudah lebih dari 3 bulan. Harap ganti password untuk melanjutkan.');
        }
        session([
            'user_id' => $user->id,
            'user' => $user,
            'logged_in' => true,
            'last_activity' => time()
        ]);

        return redirect('/dashboard')->with('success', 'Selamat datang, ' . $user->name . '!');
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Anda berhasil logout.');
    }

    public function showChangePasswordForm()
    {
        if (!session('logged_in') || !session('user_id')) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        return view('auth.change-password');
    }

    public function changePassword(Request $request)
    {
        if (!session('logged_in') || !session('user_id')) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $request->validate([
            'current_password' => 'required',
            'new_password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).+$/',
                'confirmed',
            ],
        ], [
            'current_password.required' => 'Password saat ini harus diisi.',
            'new_password.required' => 'Password baru harus diisi.',
            'new_password.min' => 'Password baru minimal 8 karakter.',
            'new_password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = User::find(session('user_id'));

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
        }

        $user->updatePassword($request->new_password);
        session([
            'user' => $user->fresh(),
            'logged_in' => true,  // ✅ Set full login
        ]);
        session()->forget(['must_change_password', 'password_warning']);

        return redirect('/dashboard')->with('success', 'Password berhasil diubah!');
    }

    /**
     * Check if user is logged in dengan session timeout
     */
    public static function isLoggedIn()
    {
        if (!session('logged_in') || !session('user_id')) {
            return false;
        }

        $lastActivity = session('last_activity', 0);
        $currentTime = time();

        if ($lastActivity === 0) {
            session(['last_activity' => $currentTime]);
            return true;
        }

        if (($currentTime - $lastActivity) > self::SESSION_TIMEOUT) {
            return false;
        }

        session(['last_activity' => $currentTime]);
        return true;
    }

    /**
     * Get current logged in user
     */
    public static function user()
    {
        if (self::isLoggedIn()) {
            return session('user');
        }
        return null;
    }

    public static function redirectIfNotLoggedIn()
    {
        // ✅ FORCE PASSWORD: Cek apakah user dalam status must change password
        if (session('must_change_password')) {
            // Allow akses ke route change password dan logout
            $currentRoute = Route::currentRouteName();
            if ($currentRoute !== 'password.change' && $currentRoute !== 'password.update' && $currentRoute !== 'logout') {
                return redirect('/dashboard/change-password')
                    ->with('warning', 'Anda harus mengganti password terlebih dahulu untuk melanjutkan.');
            }
            // Jika sudah di route change password, allow akses (return null)
            return null;
        }

        $hasSession = session()->has('user_id');
        $isLoggedIn = self::isLoggedIn();

        if (!$isLoggedIn) {
            if ($hasSession) {
                session()->forget([
                    'user_id',
                    'user',
                    'logged_in',
                    'last_activity',
                    'password_warning',
                    'must_change_password'  // ✅ Hapus flag force juga
                ]);

                $message = 'Session expired setelah 1 jam tidak aktif. Silakan login kembali.';
            } else {
                $message = 'Silakan login terlebih dahulu.';
            }

            return redirect('/login')->with('error', $message);
        }

        return null;
    }
    /**
     * Get remaining session time dalam menit
     */
    public static function getSessionRemainingTime()
    {
        if (!session('logged_in')) {
            return 0;
        }

        $lastActivity = session('last_activity', 0);
        $elapsed = time() - $lastActivity;
        $remaining = self::SESSION_TIMEOUT - $elapsed;

        return max(0, ceil($remaining / 60));
    }

    /**
     * Check apakah session akan expired dalam X menit
     */
    public static function isSessionExpiringSoon($minutes = 5)
    {
        return self::getSessionRemainingTime() <= $minutes && self::getSessionRemainingTime() > 0;
    }
}
