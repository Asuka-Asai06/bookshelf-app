<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_ログインできる(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'token',
            ]);
    }

    public function test_ログインするとトークンが発行される(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'token',
            ]);

        $this->assertCount(1, $user->fresh()->tokens);

        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_メールアドレス未入力ではログインできない(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => '',
            'password' => 'password',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'email',
            ]);
    }

    public function test_メールアドレス形式が不正ではログインできない(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'abc',
            'password' => 'password',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'email',
            ]);
    }

    public function test_パスワード未入力ではログインできない(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'password',
            ]);
    }

    public function test_認証情報が間違っているとログインできない(): void
    {
        User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response
            ->assertUnauthorized()
            ->assertJson([
                'message' => 'メールアドレスまたはパスワードが正しくありません。',
            ]);
    }
}
