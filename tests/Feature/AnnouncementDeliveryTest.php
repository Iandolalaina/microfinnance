<?php

namespace Tests\Feature;

use App\Livewire\AnnouncementForm;
use App\Models\Announcement;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AnnouncementDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_announcement_is_visible_to_clients_in_the_matching_region(): void
    {
        $zone = Zone::create(['name' => 'Atsinanana']);
        $fokontanyZone = Zone::create(['name' => 'Fokontany Ambohipo']);
        $publisher = User::factory()->create(['role' => 'agent', 'phone' => '0340000001']);
        $member = User::factory()->create([
            'role' => 'client',
            'phone' => '0340000002',
            'region' => 'Atsinanana',
            'fokontany' => 'Ankirihiry',
        ]);
        $otherMember = User::factory()->create([
            'role' => 'client',
            'phone' => '0340000003',
            'region' => 'Boeny',
            'fokontany' => 'Mahajanga I',
        ]);
        $fokontanyMember = User::factory()->create([
            'role' => 'client',
            'phone' => '0340000006',
            'region' => 'Analamanga',
            'fokontany' => 'Ambohipo',
        ]);

        Announcement::create([
            'title' => 'Information fokontany',
            'content' => 'Information pour Ambohipo.',
            'zone_id' => $fokontanyZone->id,
            'created_by' => $publisher->id,
            'published_at' => now(),
        ]);

        Announcement::create([
            'title' => 'Information régionale',
            'content' => 'Réunion de la région Atsinanana.',
            'zone_id' => $zone->id,
            'created_by' => $publisher->id,
            'published_at' => now(),
        ]);

        $this->actingAs($member)->get('/client/dashboard')
            ->assertOk()
            ->assertSee('Information régionale');

        $this->actingAs($otherMember)->get('/client/dashboard')
            ->assertOk()
            ->assertDontSee('Information régionale')
            ->assertDontSee('Information fokontany');

        $this->actingAs($fokontanyMember)->get('/client/dashboard')
            ->assertOk()
            ->assertSee('Information fokontany')
            ->assertDontSee('Information régionale');
    }

    public function test_member_can_store_and_update_a_push_subscription(): void
    {
        $member = User::factory()->create(['role' => 'client', 'phone' => '0340000004']);
        $endpoint = 'https://push.example.test/subscription/member-1';
        $subscription = [
            'endpoint' => $endpoint,
            'keys' => [
                'p256dh' => 'member-public-key',
                'auth' => 'member-auth-token',
            ],
        ];

        $this->actingAs($member)
            ->postJson(route('push-subscriptions.store'), $subscription)
            ->assertOk();

        $this->actingAs($member)
            ->postJson(route('push-subscriptions.store'), array_replace_recursive($subscription, [
                'keys' => ['p256dh' => 'updated-public-key'],
            ]))
            ->assertOk();

        $this->assertDatabaseCount('push_subscriptions', 1);
        $this->assertDatabaseHas('push_subscriptions', [
            'user_id' => $member->id,
            'endpoint_hash' => hash('sha256', $endpoint),
            'public_key' => 'updated-public-key',
        ]);
    }

    public function test_push_subscription_rejects_local_http_endpoints(): void
    {
        $member = User::factory()->create(['role' => 'client', 'phone' => '0340000007']);

        $this->actingAs($member)
            ->postJson(route('push-subscriptions.store'), [
                'endpoint' => 'http://127.0.0.1/private-service',
                'keys' => [
                    'p256dh' => 'member-public-key',
                    'auth' => 'member-auth-token',
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('endpoint');

        $this->assertDatabaseCount('push_subscriptions', 0);
    }

    public function test_admin_can_publish_an_announcement_without_push_keys(): void
    {
        config([
            'webpush.vapid.public_key' => null,
            'webpush.vapid.private_key' => null,
        ]);

        $admin = User::factory()->create(['role' => 'admin', 'phone' => '0340000005']);

        $this->actingAs($admin)
            ->get('/admin/announcements')
            ->assertOk()
            ->assertSee('Publier une annonce');

        Livewire::actingAs($admin)
            ->test(AnnouncementForm::class)
            ->set('title', 'Information importante')
            ->set('content', 'Le bureau sera fermé samedi.')
            ->call('publish')
            ->assertHasNoErrors()
            ->assertSet('published', true)
            ->assertSee('Les notifications push ne sont pas configurées');

        $this->assertDatabaseHas('announcements', [
            'title' => 'Information importante',
            'created_by' => $admin->id,
        ]);
    }
}