<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_name_required()
    {
        $this->from('/register')
            ->post('/register', [
                'name' => '',
                'email' => 'test1@gmail.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['name' => 'お名前を入力してください']);
    }

    public function test_email_required()
    {
        $this->from('/register')
            ->post('/register', [
                'name' => 'テストユーザー',
                'email' => '',
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    public function test_password_min_8()
    {
        $this->from('/register')
            ->post('/register', [
                'name' => 'テストユーザー',
                'email' => 'test2@gmail.com',
                'password' => 'test',
                'password_confirmation' => 'test',
            ])
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['password' => 'パスワードは8文字以上で入力してください']);
    }

    public function test_password_confirmation_same_password()
    {
        $this->from('/register')
            ->post('/register', [
                'name' => 'テストユーザー',
                'email' => 'test@example.com',
                'password' => 'password',
                'password_confirmation' => 'passw0rd',
            ])
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['password_confirmation' => 'パスワードと一致しません']);
    }

    public function test_password_required()
    {
        $this->from('/register')
            ->post('/register', [
                'name' => 'テストユーザー',
                'email' => 'test@example.com',
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    public function test_register_success()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test1@gmail.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('attendance.stamp'));
    }

}
