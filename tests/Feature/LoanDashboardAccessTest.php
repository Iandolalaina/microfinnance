<?php

namespace Tests\Feature;

use App\Livewire\ClientDashboard;
use App\Models\Loan;
use App\Models\LoanSchedule;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LoanDashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_sees_interest_and_due_dates_only_for_their_own_active_loan(): void
    {
        $client = User::factory()->create([
            'phone' => '0340000011',
            'name' => 'Client suivi',
            'role' => 'client',
        ]);
        $otherClient = User::factory()->create([
            'phone' => '0340000012',
            'name' => 'Autre client',
            'role' => 'client',
        ]);

        $this->createActiveLoan($client, 'Dossier visible');
        $this->createActiveLoan($otherClient, 'Dossier privé');
        $this->actingAs($client);

        Livewire::test(ClientDashboard::class)
            ->assertSee('Intérêts totaux')
            ->assertSee('20 000 Ar')
            ->assertSee('dont 10 000 Ar d’intérêts')
            ->assertDontSee('Dossier privé');
    }

    public function test_admin_sees_active_loan_interest_and_installment_due_dates(): void
    {
        $admin = User::factory()->create([
            'phone' => '0340000013',
            'role' => 'admin',
        ]);
        $client = User::factory()->create([
            'phone' => '0340000014',
            'name' => 'Client administré',
            'role' => 'client',
        ]);
        $this->createActiveLoan($client, 'Dossier administré');
        $this->actingAs($admin);

        $this->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Dossiers de crédit en cours')
            ->assertSee('Client administré')
            ->assertSee('20 000 Ar')
            ->assertSee('15 novembre 2026');
    }

    public function test_agent_sees_active_loan_details_for_clients_in_their_zone(): void
    {
        $zone = Zone::create(['name' => 'Zone Agent']);
        $otherZone = Zone::create(['name' => 'Autre Zone']);
        $agent = User::factory()->create([
            'phone' => '0340000015',
            'role' => 'agent',
            'zone_id' => $zone->id,
        ]);
        $client = User::factory()->create([
            'phone' => '0340000016',
            'name' => 'Client de la zone',
            'role' => 'client',
            'zone_id' => $zone->id,
        ]);
        $otherClient = User::factory()->create([
            'phone' => '0340000017',
            'name' => 'Client hors zone',
            'role' => 'client',
            'zone_id' => $otherZone->id,
        ]);
        $this->createActiveLoan($client, 'Dossier agent');
        $this->createActiveLoan($otherClient, 'Dossier hors zone');
        $this->actingAs($agent);

        $this->get('/agent/dashboard')
            ->assertOk()
            ->assertSee('20 000 Ar')
            ->assertSee('15 novembre 2026')
            ->assertSee('Client de la zone')
            ->assertDontSee('Client hors zone');
    }

    private function createActiveLoan(User $client, string $contractNumber): Loan
    {
        $loan = Loan::create([
            'user_id' => $client->id,
            'amount' => 100000,
            'interest_rate' => 20,
            'total_repayable' => 120000,
            'duration_months' => 2,
            'status' => 'active',
            'contract_number' => $contractNumber,
        ]);

        LoanSchedule::create([
            'loan_id' => $loan->id,
            'installment_number' => 1,
            'due_date' => '2026-11-15',
            'principal_due' => 50000,
            'interest_due' => 10000,
            'amount_due' => 60000,
            'amount_paid' => 0,
            'status' => 'pending',
        ]);

        return $loan;
    }
}
