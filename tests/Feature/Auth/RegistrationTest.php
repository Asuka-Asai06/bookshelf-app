<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_必須項目が入力されていればユーザー登録できる(): void
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('books.index'));

        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
        ]);

        $this->assertAuthenticated();
    }

    public function test_必須項目がない場合登録できない(): void
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => '',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors([
            'name',
            'email',
            'password',
        ]);

        $this->assertGuest();
    }

    public function test_登録済みのメールアドレスでは登録できない(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->post('/register', [
            'name' => '新規ユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'email',
        ]);
    }

    public function test_名前が未入力の場合はエラーになる(): void
    {
        $response = $this->post('/register', [
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_名前が256文字以上の場合はエラーになる(): void
    {
        $response = $this->post('/register', [
            'name' => str_repeat('あ', 256),
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_email形式が不正な場合はエラーになる(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_emailが256文字以上の場合はエラーになる(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => str_repeat('a', 256).'@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_パスワードと確認用パスワードが不一致の場合はエラーになる(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertSessionHasErrors(['password_confirmation']);
        $response->assertSessionHasErrors([
            'password_confirmation' => 'パスワードが一致しません。',
        ]);
    }
}
