<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_マイ読書レポート画面を表示できる(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('reports.index'));

        $response->assertOk();

        $response->assertViewIs('reports.index');
    }

    public function test_未ログインではレポート画面を表示できない(): void
    {
        $response = $this->get(route('reports.index'));

        $response->assertRedirect('/login');
    }
}
