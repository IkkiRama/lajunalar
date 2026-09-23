<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_renders_logout_confirmation(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Keluar dari LajuNalar Admin?');
    }
}
