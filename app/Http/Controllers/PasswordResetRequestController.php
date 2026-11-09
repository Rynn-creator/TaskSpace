<?php

namespace App\Http\Controllers;

use App\Models\PasswordResetRequest as PasswordResetRequestModel;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetRequestController extends Controller
{
    public function create()
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);
        $email = Str::lower($validated['email']);

        if (! PasswordResetRequestModel::whereRaw('LOWER(email) = ?', [$email])
            ->where('status', 'pending')
            ->exists()) {
            $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

            PasswordResetRequestModel::create([
                'user_id' => $user?->id,
                'name' => $user?->name,
                'email' => $email,
            ]);
        }

        return back()->with('status', 'Jika email tersebut terdaftar, permintaan reset akan ditinjau admin.');
    }

    public function edit(string $token, Request $request)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($user?->isAdmin()) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __(Password::INVALID_TOKEN)]);
        }

        $status = Password::reset(
            $validated,
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('tasks.index')->with('success', 'Password berhasil diperbarui.');
    }
}
