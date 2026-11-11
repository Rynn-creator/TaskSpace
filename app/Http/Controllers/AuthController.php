<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Halaman Form Register & Login
    public function showLogin() { return view('auth.login'); }
    public function showRegister() { return view('auth.register'); }

    // Proses Register
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        ActivityLog::create([
            'user_id' => $user->id,
            'type' => 'registered',
            'action' => 'mendaftar ke TaskSpace',
        ]);

        Auth::login($user);
        return redirect()->route('tasks.index');
    }

    // Proses Login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials + ['is_suspended' => false])) {
            $request->session()->regenerate();
            ActivityLog::create([
                'user_id' => $request->user()->id,
                'type' => 'logged_in',
                'action' => 'login ke TaskSpace',
            ]);

            return redirect()->intended('/');
        }

        return back()->withErrors(['email' => 'Email atau password salah atau akun tidak aktif.']);
    }

    // Proses Logout
    public function logout(Request $request)
    {
        ActivityLog::create([
            'user_id' => $request->user()->id,
            'type' => 'logged_out',
            'action' => 'logout dari TaskSpace',
        ]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}