<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_required()
    {
        $user = User::forceCreate([
            'name' => 'テストユーザー',
            'email' => 'test2@gmail.com',
            'password' => Hash::make('password'),
        ]);

        $this->from('/login')
            ->post('/login', [
                'email' => '',
                'password' => 'password',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    public function test_password_required()
    {
        $user = User::forceCreate([
            'name' => 'テストユーザー',
            'email' => 'test3@gmail.com',
            'password' => Hash::make('password'),
        ]);

        $this->from('/login')
            ->post('/login', [
                'email' => 'test2@gmail.com',
                'password' => '',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    public function test_login_unregistered()
    {
        $user = User::forceCreate([
            'name' => 'テストユーザー',
            'email' => 'test3@gmail.com',
            'password' => Hash::make('password'),
        ]);

        $this->from('/login')
            ->post('/login', [
                'email' => 'testing@example.com',
                'password' => 'password',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);
    }
}
