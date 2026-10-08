<?php

namespace Tests\Feature;

use App\Livewire\UserManagement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_edit_and_deactivate_user_accounts(): void
    {
        $admin = User::factory()->create([
            'phone' => '0340000001',
            'role' => 'admin',
            'is_active' => true,
        ]);
        $this->actingAs($admin);

        Livewire::test(UserManagement::class)
            ->set('name', 'Client créé')
            ->set('phone', '0340000002')
            ->set('password', 'secret123')
            ->set('role', 'client')
            ->call('create')
            ->assertHasNoErrors();

        $client = User::where('phone', '0340000002')->firstOrFail();
        $this->assertSame('Client créé', $client->name);

        Livewire::test(UserManagement::class)
            ->call('edit', $client->id)
            ->set('name', 'Client modifié')
            ->set('phone', '0340000003')
            ->set('password', '')
            ->set('role', 'agent')
            ->call('update')
            ->assertHasNoErrors();

        $client->refresh();
        $this->assertSame('Client modifié', $client->name);
        $this->assertSame('0340000003', $client->phone);
        $this->assertSame('agent', $client->role);

        Livewire::test(UserManagement::class)
            ->call('toggleActive', $client->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $client->id,
            'is_active' => false,
        ]);
    }

    public function test_admin_cannot_change_own_role_or_disable_own_account(): void
    {
        $admin = User::factory()->create([
            'phone' => '0340000004',
            'role' => 'admin',
            'is_active' => true,
        ]);
        $this->actingAs($admin);

        Livewire::test(UserManagement::class)
            ->call('edit', $admin->id)
            ->set('role', 'client')
            ->call('update')
            ->assertHasErrors('role');

        Livewire::test(UserManagement::class)
            ->call('toggleActive', $admin->id);

        $this->assertTrue($admin->fresh()->is_active);
        $this->assertSame('admin', $admin->fresh()->role);
    }
}
