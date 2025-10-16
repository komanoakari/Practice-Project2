<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use App\Models\User;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_register_and_email_verification()
    {
        Notification::fake();

        $this->from('/register')
            ->post('/register', [
                'name' => 'テストユーザー',
                'email' => 'test4@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ])->assertRedirect(route('attendance.stamp'));

        $user = User::where('email', 'test4@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->email_verified_at);

        Notification::assertSentTo($user,VerifyEmail::class);
    }

    public function test_user_can_email_verification_display()
    {
        $user = User::forceCreate([
            'name' => '未認証ユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => null,
        ]);

        $this->ActingAs($user);

        $this->get(route('verification.notice'))
            ->assertOk()
            ->assertSee('認証はこちらから')
            ->assertSee('<a href="http://localhost:8025" class="btn">認証はこちらから</a>', false);
    }

    public function test_verified_redirects_to_attendance_stamp()
    {
        $user = User::forceCreate([
            'name' => '未認証ユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => null,
        ]);

        $this->actingAs($user);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' =>sha1($user->email),
            ]
            );

        $response = $this->get($url);

        $response->assertRedirect(route('attendance.stamp'));

        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}