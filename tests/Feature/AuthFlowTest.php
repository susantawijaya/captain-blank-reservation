<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_must_login_before_accessing_customer_reservation_page(): void
    {
        $response = $this->get(route('reservations.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_customer_is_redirected_away_from_admin_dashboard(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $response = $this->actingAs($customer)->get(route('admin.dashboard'));

        $response->assertRedirect(route('customer.dashboard'));
    }

    public function test_login_redirects_admin_to_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'password123',
            'role' => 'admin',
        ]);

        $response = $this->post(route('login.store'), [
            'email' => $admin->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_login_redirects_customer_to_customer_dashboard(): void
    {
        $customer = User::factory()->create([
            'email' => 'customer@example.com',
            'password' => 'password123',
            'role' => 'customer',
        ]);

        $response = $this->post(route('login.store'), [
            'email' => $customer->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('customer.dashboard'));
        $this->assertAuthenticatedAs($customer);
    }

    public function test_customer_registration_creates_customer_account(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Pelanggan Baru',
            'email' => 'pelanggan@example.com',
            'phone' => '08123456789',
            'address' => 'Jl. Pantai',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('customer.dashboard'));
        $this->assertDatabaseHas('users', [
            'email' => 'pelanggan@example.com',
            'role' => 'customer',
            'is_master_admin' => false,
        ]);
    }

    public function test_only_master_admin_can_access_user_management(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_master_admin' => false,
        ]);

        $masterAdmin = User::factory()->create([
            'role' => 'admin',
            'is_master_admin' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($masterAdmin)
            ->get(route('admin.users.index'))
            ->assertOk();
    }

    public function test_login_page_does_not_show_password_reset_link(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('Reset di sini');
    }

    public function test_database_seeder_creates_default_master_admin(): void
    {
        $this->seed();

        $this->assertDatabaseHas('users', [
            'email' => 'admin@captainblank.com',
            'role' => 'admin',
            'is_master_admin' => true,
        ]);
    }
}
