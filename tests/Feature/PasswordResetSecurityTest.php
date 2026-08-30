<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_requesting_a_reset_does_not_immediately_change_the_password(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $oldPasswordHash = $user->password;

        $this->post(route('auth.forgot-password'), ['email' => $user->email])
            ->assertOk();

        $this->assertSame($oldPasswordHash, $user->fresh()->password);
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_known_and_unknown_emails_get_the_same_public_response(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $known = $this->post(route('auth.forgot-password'), ['email' => $user->email]);
        $unknown = $this->post(route('auth.forgot-password'), ['email' => 'unknown@example.com']);

        $known->assertOk();
        $unknown->assertOk();
        $this->assertSame($known->getContent(), $unknown->getContent());
    }

    public function test_a_valid_token_can_be_used_only_to_set_a_new_password(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $token = null;

        $this->post(route('auth.forgot-password'), ['email' => $user->email]);
        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token) {
            $token = $notification->token;

            return true;
        });

        $payload = [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewSecure1234',
            'password_confirmation' => 'NewSecure1234',
        ];

        $this->post(route('auth.reset-password'), $payload)
            ->assertRedirect(route('auth.sign-in.page'));

        $this->assertTrue(Hash::check('NewSecure1234', $user->fresh()->password));
        $this->post(route('auth.reset-password'), $payload)->assertSessionHasErrors('email');
    }
}
