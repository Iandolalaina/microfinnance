<?php

namespace Tests\Feature;

use App\Livewire\UserManagement;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_agent_can_manage_only_clients_in_their_zone_and_upload_profile_photos(): void
    {
        Storage::fake('public');
        $zone = Zone::create(['name' => 'Zone Agent']);
        $otherZone = Zone::create(['name' => 'Autre Zone']);
        $agentPhoto = 'profiles/agent.png';
        Storage::disk('public')->put($agentPhoto, 'agent photo');
        $agent = User::factory()->create([
            'phone' => '0340000010',
            'role' => 'agent',
            'zone_id' => $zone->id,
            'profile_photo' => $agentPhoto,
        ]);
        $oldClientPhoto = 'profiles/existing-client.png';
        Storage::disk('public')->put($oldClientPhoto, 'old client photo');
        $visibleClient = User::factory()->create([
            'phone' => '0340000011',
            'name' => 'Client de la zone',
            'role' => 'client',
            'zone_id' => $zone->id,
            'profile_photo' => $oldClientPhoto,
        ]);
        $otherClient = User::factory()->create([
            'phone' => '0340000012',
            'name' => 'Client hors zone',
            'role' => 'client',
            'zone_id' => $otherZone->id,
        ]);
        $this->actingAs($agent);

        $this->get('/agent/dashboard')
            ->assertOk()
            ->assertSee(Storage::disk('public')->url($agentPhoto), false);

        $this->get('/agent/users')
            ->assertOk()
            ->assertSee('Client de la zone')
            ->assertDontSee('Client hors zone');

        Livewire::test(UserManagement::class)
            ->call('toggleForm')
            ->assertSee('role="dialog"', false)
            ->assertSee('id="user-form-title"', false)
            ->call('closeForm')
            ->assertDontSee('id="user-form-title"', false);

        Livewire::test(UserManagement::class)
            ->call('viewUser', $visibleClient->id)
            ->assertSee('role="dialog"', false)
            ->assertSee('aria-modal="true"', false)
            ->assertSee(Storage::disk('public')->url($oldClientPhoto), false)
            ->assertSee('Matricule')
            ->assertSee('Zone Agent');

        try {
            Livewire::test(UserManagement::class)->call('viewUser', $otherClient->id);
            $this->fail('An agent must not be able to access a client outside their zone.');
        } catch (ModelNotFoundException) {
            $this->assertDatabaseHas('users', ['id' => $otherClient->id]);
        }

        Livewire::test(UserManagement::class)
            ->set('name', 'Nouveau client')
            ->set('phone', '0340000013')
            ->set('password', 'secret123')
            ->set('profilePhoto', UploadedFile::fake()->image('client.png'))
            ->call('create')
            ->assertHasNoErrors();

        $newClient = User::where('phone', '0340000013')->firstOrFail();
        $this->assertSame('client', $newClient->role);
        $this->assertSame($zone->id, $newClient->zone_id);
        Storage::disk('public')->assertExists($newClient->profile_photo);

        $this->get('/agent/users')
            ->assertSee(Storage::disk('public')->url($newClient->profile_photo), false);

        Livewire::test(UserManagement::class)
            ->call('edit', $visibleClient->id)
            ->assertSee('role="dialog"', false)
            ->assertSee('h-40 w-40 rounded-xl', false)
            ->assertSee(Storage::disk('public')->url($oldClientPhoto), false)
            ->set('name', 'Client renommé')
            ->set('profilePhoto', UploadedFile::fake()->image('updated-client.png'))
            ->call('update')
            ->assertHasNoErrors();

        $this->assertSame('Client renommé', $visibleClient->fresh()->name);
        $this->assertNotSame($oldClientPhoto, $visibleClient->fresh()->profile_photo);
        Storage::disk('public')->assertMissing($oldClientPhoto);
        Storage::disk('public')->assertExists($visibleClient->fresh()->profile_photo);

        Livewire::test(UserManagement::class)
            ->call('delete', $newClient->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('users', ['id' => $newClient->id]);
        Storage::disk('public')->assertMissing($newClient->profile_photo);
    }

    public function test_agent_cannot_edit_disable_or_delete_an_admin_account(): void
    {
        $zone = Zone::create(['name' => 'Zone Agent']);
        $agent = User::factory()->create([
            'phone' => '0340000020',
            'role' => 'agent',
            'zone_id' => $zone->id,
        ]);
        $admin = User::factory()->create([
            'phone' => '0340000021',
            'role' => 'admin',
            'is_active' => true,
        ]);
        $this->actingAs($agent);

        foreach (['edit', 'toggleActive', 'delete'] as $action) {
            try {
                Livewire::test(UserManagement::class)->call($action, $admin->id);
                $this->fail("An agent must not be able to {$action} an administrator account.");
            } catch (ModelNotFoundException) {
                $this->assertDatabaseHas('users', [
                    'id' => $admin->id,
                    'role' => 'admin',
                    'is_active' => true,
                ]);
            }
        }
    }
}
