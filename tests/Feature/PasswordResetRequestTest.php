<?php

namespace Tests\Feature;

use App\Models\PasswordResetRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class PasswordResetRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_a_password_reset_without_disclosing_account_existence(): void
    {
        $user = User::factory()->create(['name' => 'Nadif']);

        $this->get(route('password.request'))->assertOk();
        $this->post(route('password.request.store'), ['email' => $user->email])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('password_reset_requests', [
            'user_id' => $user->id,
            'name' => 'Nadif',
            'email' => strtolower($user->email),
            'status' => 'pending',
        ]);

        $this->post(route('password.request.store'), ['email' => 'unknown@example.com'])
            ->assertSessionHas('status');
        $this->assertDatabaseHas('password_reset_requests', [
            'email' => 'unknown@example.com',
            'user_id' => null,
        ]);
    }

    public function test_duplicate_pending_requests_are_deduplicated(): void
    {
        $user = User::factory()->create();

        $this->post(route('password.request.store'), ['email' => $user->email]);
        $this->post(route('password.request.store'), ['email' => strtoupper($user->email)]);

        $this->assertSame(
            1,
            PasswordResetRequest::where('email', strtolower($user->email))
                ->where('status', 'pending')
                ->count(),
        );
    }

    public function test_admin_can_review_request_and_send_reset_link_to_member(): void
    {
        Notification::fake();
        config(['mail.default' => 'smtp']);
        $user = User::factory()->create(['name' => 'Member']);
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $this->post(route('password.request.store'), ['email' => $user->email]);
        $resetRequest = PasswordResetRequest::firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.password-requests.index'))
            ->assertOk()
            ->assertSee('Request Reset Password')
            ->assertSee('Member')
            ->assertSee($user->email)
            ->assertSee('Setujui & Kirim Link');

        $this->actingAs($admin)
            ->post(route('admin.password-requests.send', $resetRequest))
            ->assertRedirect()
            ->assertSessionHas('success');

        Notification::assertSentTo($user, ResetPassword::class);
        $this->assertDatabaseHas('password_reset_requests', [
            'id' => $resetRequest->id,
            'status' => 'sent',
            'processed_by' => $admin->id,
        ]);
    }

    public function test_admin_is_not_told_email_was_sent_when_mailer_is_only_a_log(): void
    {
        config(['mail.default' => 'log']);
        $user = User::factory()->create();
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $resetRequest = PasswordResetRequest::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.password-requests.index'))
            ->post(route('admin.password-requests.send', $resetRequest))
            ->assertRedirect(route('admin.password-requests.index'))
            ->assertSessionHasErrors('email');

        $this->assertSame('pending', $resetRequest->fresh()->status);
    }

    public function test_smtp_authentication_failure_returns_actionable_error_and_keeps_request_pending(): void
    {
        config(['mail.default' => 'smtp']);
        $user = User::factory()->create();
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $resetRequest = PasswordResetRequest::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);

        Password::shouldReceive('sendResetLink')
            ->once()
            ->with(['email' => $user->email])
            ->andThrow(new TransportException('SMTP authentication failed'));

        $this->actingAs($admin)
            ->from(route('admin.password-requests.index'))
            ->post(route('admin.password-requests.send', $resetRequest))
            ->assertRedirect(route('admin.password-requests.index'))
            ->assertSessionHasErrors([
                'email' => 'Email reset gagal dikirim karena server email menolak koneksi atau autentikasi. Periksa konfigurasi SMTP dan Gmail App Password di .env, lalu coba lagi.',
            ]);

        $this->assertSame('pending', $resetRequest->fresh()->status);
    }

    public function test_only_admin_can_send_links_and_unregistered_emails_cannot_be_approved(): void
    {
        $user = User::factory()->create();
        $requestRecord = PasswordResetRequest::create(['email' => 'unknown@example.com']);

        $this->actingAs($user)
            ->get(route('admin.password-requests.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.password-requests.send', $requestRecord))
            ->assertForbidden();

        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $this->actingAs($admin)
            ->post(route('admin.password-requests.send', $requestRecord))
            ->assertNotFound();

        $this->assertSame('pending', $requestRecord->fresh()->status);
    }

    public function test_password_reset_link_can_be_used_to_set_a_new_password(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))
            ->assertOk();

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewSecurePassword123',
            'password_confirmation' => 'NewSecurePassword123',
        ])->assertRedirect(route('tasks.index'));

        $this->assertTrue(Hash::check('NewSecurePassword123', $user->fresh()->password));
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_cannot_use_password_reset_link(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $token = Password::createToken($admin);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $admin->email,
            'password' => 'NewSecurePassword123',
            'password_confirmation' => 'NewSecurePassword123',
        ])->assertSessionHasErrors('email');

        $this->assertFalse(Hash::check('NewSecurePassword123', $admin->fresh()->password));
        $this->assertGuest();
    }
}
