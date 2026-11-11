<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_and_user_can_upload_and_replace_only_their_own_profile_photo(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $user = User::factory()->create();

        foreach ([$admin, $user] as $account) {
            $this->actingAs($account)
                ->patch(route('profile.update'), [
                    'name' => $account->name,
                    'profile_photo' => $this->fakePng('first.png'),
                ])
                ->assertRedirect(route('profile.edit'))
                ->assertSessionHas('success');

            $firstPath = $account->fresh()->profile_photo_path;
            $this->assertNotNull($firstPath);
            Storage::disk('public')->assertExists($firstPath);

            $this->patch(route('profile.update'), [
                'name' => $account->name,
                'profile_photo' => $this->fakePng('second.png'),
            ])->assertSessionHas('success');

            $secondPath = $account->fresh()->profile_photo_path;
            $this->assertNotSame($firstPath, $secondPath);
            Storage::disk('public')->assertMissing($firstPath);
            Storage::disk('public')->assertExists($secondPath);
        }

        $this->assertNotSame($admin->fresh()->profile_photo_path, $user->fresh()->profile_photo_path);
    }

    public function test_profile_photo_remains_available_after_sign_out_and_sign_in(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $path = $this->fakePng('avatar.png')->store('profile-photos', 'public');
        $user->forceFill(['profile_photo_path' => $path])->save();

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee(Storage::disk('public')->url($path), false);

        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect('/');

        $this->get(route('profile.edit'))
            ->assertOk()
            ->assertSee(Storage::disk('public')->url($path), false);
    }

    public function test_profile_photo_can_be_removed_and_avatar_falls_back_to_initial(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['name' => 'Profile Owner']);
        $path = $this->fakePng('avatar.png')->store('profile-photos', 'public');
        $user->forceFill(['profile_photo_path' => $path])->save();

        $this->actingAs($user)
            ->delete(route('profile.photo.destroy'))
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('success');

        $this->assertNull($user->fresh()->profile_photo_path);
        Storage::disk('public')->assertMissing($path);

        $this->get(route('profile.edit'))
            ->assertOk()
            ->assertSeeText('P')
            ->assertDontSee(Storage::disk('public')->url($path), false);
    }

    public function test_profile_update_rejects_non_images_and_images_over_two_megabytes(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->patch(route('profile.update'), [
                'name' => $user->name,
                'profile_photo' => UploadedFile::fake()->create('not-an-image.jpg', 10, 'text/plain'),
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors('profile_photo');

        $this->patch(route('profile.update'), [
            'name' => $user->name,
            'profile_photo' => $this->fakePng('too-large.png', 2 * 1024 * 1024 + 1),
        ])->assertSessionHasErrors('profile_photo');
    }

    public function test_profile_routes_require_authentication_and_other_accounts_are_not_addressable(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->get(route('profile.edit'))->assertRedirect(route('login'));
        $this->patch(route('profile.update'), ['name' => 'Changed'])->assertRedirect(route('login'));

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'user_id' => $otherUser->id,
                'name' => 'Changed current user',
                'profile_photo' => $this->fakePng('own-photo.png'),
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('success');

        $this->assertSame('Changed current user', $user->fresh()->name);
        $this->assertNotNull($user->fresh()->profile_photo_path);
        $this->assertSame($otherUser->name, $otherUser->fresh()->name);
        $this->assertNull($otherUser->fresh()->profile_photo_path);
    }

    public function test_admin_user_directory_and_details_display_the_saved_profile_photo(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $user = User::factory()->create();
        $path = $this->fakePng('avatar.png')->store('profile-photos', 'public');
        $user->forceFill(['profile_photo_path' => $path])->save();

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee(Storage::disk('public')->url($path), false);

        $this->get(route('admin.users.show', $user))
            ->assertOk()
            ->assertSee(Storage::disk('public')->url($path), false);
    }

    private function fakePng(string $name, int $size = 0): UploadedFile
    {
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/rWQAAAAASUVORK5CYII=',
            true
        );

        if ($size > strlen($png)) {
            $png .= str_repeat("\0", $size - strlen($png));
        }

        return UploadedFile::fake()->createWithContent($name, $png);
    }
}
