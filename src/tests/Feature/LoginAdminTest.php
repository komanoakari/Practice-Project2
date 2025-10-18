<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class LoginAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_required()
    {
        $admin = Admin::forceCreate([
            'name' => '管理ユーザー',
            'email' => 'admin1@gmail.com',
            'password' => Hash::make('password'),
        ]);

        $this->from('/admin/login')
            ->post('/admin/login', [
                'email' => '',
                'password' => 'password',
            ])
            ->assertRedirect('/admin/login')
            ->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    public function test_password_required()
    {
        $admin = Admin::forceCreate([
            'name' => '管理ユーザー',
            'email' => 'admin1@gmail.com',
            'password' => Hash::make('password'),
        ]);

        $this->from('/admin/login')
            ->post('/admin/login', [
                'email' => 'admin1@gmail.com',
                'password' => '',
            ])
            ->assertRedirect('/admin/login')
            ->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    public function test_login_unregistered()
    {
        $admin = Admin::forceCreate([
            'name' => '管理ユーザー',
            'email' => 'admin1@gmail.com',
            'password' => Hash::make('password'),
        ]);

        $this->from('/admin/login')
            ->post('/admin/login', [
                'email' => 'developer@gmail.com',
                'password' => 'password',
            ])
            ->assertRedirect('/admin/login')
            ->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);
    }
}
