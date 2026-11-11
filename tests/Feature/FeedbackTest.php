<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_send_feedback_and_see_admin_reply(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $this->actingAs($user)
            ->post(route('feedback.store'), ['message' => 'Tolong tambahkan fitur kalender'])
            ->assertRedirect();

        $this->assertDatabaseHas('feedbacks', [
            'user_id' => $user->id,
            'message' => 'Tolong tambahkan fitur kalender',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.feedback.index'))
            ->assertOk()
            ->assertSee('Tolong tambahkan fitur kalender')
            ->assertSee($user->email);

        $feedback = $user->feedbacks()->firstOrFail();
        $this->actingAs($admin)
            ->post(route('admin.feedback.reply', $feedback), ['admin_reply' => 'Terima kasih atas sarannya!'])
            ->assertRedirect();

        $this->actingAs($user)
            ->get(route('feedback.index'))
            ->assertOk()
            ->assertSee('Terima kasih atas sarannya!');
    }

    public function test_user_cannot_access_admin_feedback_page_or_reply(): void
    {
        $user = User::factory()->create();
        $feedback = $user->feedbacks()->create(['message' => 'Saran']);
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser)
            ->get(route('admin.feedback.index'))
            ->assertForbidden();

        $this->post(route('admin.feedback.reply', $feedback), ['admin_reply' => 'Balasan'])
            ->assertForbidden();
    }

    public function test_admin_is_redirected_to_admin_feedback_page(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $this->actingAs($admin)
            ->get(route('feedback.index'))
            ->assertRedirect(route('admin.feedback.index'));
    }

    public function test_admin_can_review_unanswered_feedback_from_feedback_page(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $user = User::factory()->create();
        $pending = $user->feedbacks()->create(['message' => 'Tolong tambahkan mode kalender']);
        $user->feedbacks()->create([
            'message' => 'Feedback yang sudah dijawab',
            'admin_reply' => 'Sudah kami catat.',
            'replied_at' => now(),
        ]);
        $admin->tasks()->create([
            'title' => 'Admin private task',
            'category' => 'Personal',
            'priority' => 'Low',
            'due_date' => '2026-12-01',
        ]);

        $feedbackPage = route('admin.feedback.index', ['status' => 'pending']);
        $this->actingAs($admin)
            ->get($feedbackPage)
            ->assertOk()
            ->assertSee('Feedback Masuk')
            ->assertSee('Tolong tambahkan mode kalender')
            ->assertSee($user->name)
            ->assertSee('Menunggu balasan')
            ->assertDontSee('Feedback yang sudah dijawab')
            ->assertDontSee('Buat Task Baru & Target Date')
            ->assertDontSee('Admin private task');

        $this->actingAs($admin)
            ->from($feedbackPage)
            ->post(route('admin.feedback.reply', $pending), [
                'admin_reply' => 'Terima kasih, masukannya kami terima.',
            ])
            ->assertRedirect($feedbackPage);

        $this->assertDatabaseHas('feedbacks', [
            'id' => $pending->id,
            'admin_reply' => 'Terima kasih, masukannya kami terima.',
        ]);
    }

    public function test_user_only_sees_their_own_feedback(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $user->feedbacks()->create(['message' => 'My feedback']);
        $otherUser->feedbacks()->create(['message' => 'Private feedback']);

        $this->actingAs($user)
            ->get(route('feedback.index'))
            ->assertOk()
            ->assertSee('My feedback')
            ->assertDontSee('Private feedback');
    }
}
