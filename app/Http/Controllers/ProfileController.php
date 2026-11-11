<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = $request->user();
        $oldPhotoPath = $user->profile_photo_path;
        $attributes = ['name' => $validated['name']];

        if ($request->hasFile('profile_photo')) {
            $newPhotoPath = $request->file('profile_photo')->store('profile-photos', 'public');

            if (! $newPhotoPath) {
                return to_route('profile.edit')
                    ->withInput($request->only('name'))
                    ->withErrors(['profile_photo' => 'Foto profil gagal disimpan. Silakan coba lagi.']);
            }

            $attributes['profile_photo_path'] = $newPhotoPath;
        }

        if (! $user->forceFill($attributes)->save()) {
            if (isset($newPhotoPath)) {
                Storage::disk('public')->delete($newPhotoPath);
            }

            return to_route('profile.edit')
                ->withInput($request->only('name'))
                ->withErrors(['profile' => 'Perubahan profil gagal disimpan. Silakan coba lagi.']);
        }

        if (isset($newPhotoPath) && ! $this->deleteStoredPhoto($oldPhotoPath, $user->id)) {
            Log::warning('Old profile photo could not be deleted after replacement.', [
                'user_id' => $user->id,
                'photo_path' => $oldPhotoPath,
            ]);

            return to_route('profile.edit')->with('warning', 'Profil tersimpan, tetapi foto lama gagal dihapus dari penyimpanan.');
        }

        return to_route('profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }

    public function destroyPhoto(Request $request): RedirectResponse
    {
        $user = $request->user();
        $oldPhotoPath = $user->profile_photo_path;

        if (! $oldPhotoPath) {
            return to_route('profile.edit')->with('success', 'Profil sudah menggunakan avatar inisial.');
        }

        if (! $user->forceFill(['profile_photo_path' => null])->save()) {
            return to_route('profile.edit')->withErrors(['profile_photo' => 'Foto profil gagal dihapus. Silakan coba lagi.']);
        }

        if (! $this->deleteStoredPhoto($oldPhotoPath, $user->id)) {
            Log::warning('Profile photo could not be deleted from public storage.', [
                'user_id' => $user->id,
                'photo_path' => $oldPhotoPath,
            ]);

            return to_route('profile.edit')->with('warning', 'Avatar inisial sudah dipulihkan, tetapi file foto gagal dibersihkan.');
        }

        return to_route('profile.edit')->with('success', 'Foto profil berhasil dihapus.');
    }

    private function deleteStoredPhoto(?string $path, int $userId): bool
    {
        if (! $path || ! Str::startsWith($path, 'profile-photos/')) {
            return true;
        }

        if (User::query()
            ->where('profile_photo_path', $path)
            ->where('id', '!=', $userId)
            ->exists()) {
            return true;
        }

        $disk = Storage::disk('public');

        return ! $disk->exists($path) || $disk->delete($path);
    }
}
