@php
    $profilePhotoUrl = $profileUser->profile_photo_path
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($profileUser->profile_photo_path)
        : null;
@endphp
<span class="{{ $avatarClass }}">
    @if($profilePhotoUrl)
        <img src="{{ $profilePhotoUrl }}" alt="Foto profil {{ $profileUser->name }}" class="h-full w-full rounded-full object-cover">
    @else
        {{ strtoupper(substr($profileUser->name, 0, 1)) }}
    @endif
</span>
