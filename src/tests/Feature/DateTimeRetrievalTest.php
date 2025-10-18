<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DateTimeRetrievalTest extends TestCase
{
    use RefreshDatabase;

    public function test_format_now_returns_expected_format()
    {
        $user = User::forceCreate([
            'name' => 'テストユーザー',
            'email' => 'test3@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $today = now();

        $date = $today->format('Y年m月d日');
        $dayOfWeek = ['日', '月', '火', '水', '木', '金', '土'][$today->dayOfWeek];
        $expectedDate = $date . '(' . $dayOfWeek . ')';

        $this->get('/attendance')
            ->assertOk()
            ->assertSee($expectedDate);
    }
}
