<?php

namespace Tests\Feature;

use App\Events\PaymentReceived;
use App\Http\Requests\CreateLoanRequest;
use App\Livewire\ClientLoanRequest;
use App\Models\Group;
use App\Models\Loan;
use App\Models\LoanType;
use App\Models\Payment;
use App\Models\User;
use App\Services\LoanService;
use App\Services\Mvola\FakeMvolaService;
use App\Services\PaymentConfirmationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class LoanServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_weekly_schedule_spreads_amount_and_applies_grace_period(): void
    {
        $schedule = app(LoanService::class)->previewSchedule([
            'amount' => 100000,
            'interest_rate' => 5,
            'duration_months' => 3,
            'repayment_frequency' => 'weekly',
            'first_due_date' => '2026-01-01',
            'grace_period_days' => 14,
        ]);

        $this->assertCount(14, $schedule);
        $this->assertSame('2026-01-15', $schedule->first()['due_date']);
        $this->assertSame('2026-04-16', $schedule->last()['due_date']);
        $this->assertEquals(100000, $schedule->sum('principal_due'));
        $this->assertEqualsWithDelta(15000, $schedule->sum('interest_due'), 0.01);
        $this->assertEqualsWithDelta(115000, $schedule->sum('amount_due'), 0.01);
        $this->assertSame('pending', $schedule->first()['status']);
    }

    public function test_biweekly_schedule_uses_fourteen_day_intervals(): void
    {
        $schedule = app(LoanService::class)->previewSchedule([
            'amount' => 30000,
            'interest_rate' => 0,
            'duration_months' => 2,
            'repayment_frequency' => 'biweekly',
            'first_due_date' => '2026-01-31',
        ]);

        $this->assertCount(5, $schedule);
        $this->assertSame('2026-01-31', $schedule->first()['due_date']);
        $this->assertSame('2026-02-28', $schedule->get(2)['due_date']);
        $this->assertSame('2026-03-28', $schedule->last()['due_date']);
        $this->assertEquals(30000, $schedule->sum('amount_due'));
    }

    public function test_loan_schedule_status_tracks_partial_and_completed_payments(): void
    {
        $client = User::create([
            'name' => 'Membre test',
            'phone' => '0340000001',
            'password' => 'password',
            'role' => 'client',
            'is_active' => true,
        ]);
        $type = LoanType::create([
            'code' => 'TEST',
            'name' => 'Credit de test',
            'allowed_frequencies' => ['monthly'],
        ]);
        $loan = Loan::create([
            'user_id' => $client->id,
            'loan_type_id' => $type->id,
            'amount' => 100,
            'interest_rate' => 0,
            'duration_months' => 1,
            'repayment_frequency' => 'monthly',
            'first_due_date' => now()->addMonth()->toDateString(),
            'total_repayable' => 100,
        ]);
        $schedule = $loan->loanSchedules()->create([
            'installment_number' => 1,
            'due_date' => now()->addMonth()->toDateString(),
            'principal_due' => 100,
            'interest_due' => 0,
            'amount_due' => 100,
        ]);

        Storage::fake('public');
        Event::fake([PaymentReceived::class]);

        $firstPayment = Payment::create([
            'loan_id' => $loan->id,
            'loan_schedule_id' => $schedule->id,
            'user_id' => $client->id,
            'amount' => 40,
            'method' => 'cash',
            'status' => 'pending',
        ]);
        $confirmation = app(PaymentConfirmationService::class);
        $confirmedPartialPayment = $confirmation->confirm($firstPayment);
        $partial = $schedule->refresh();

        $this->assertSame('partial', $partial->status);
        $this->assertEquals(40, $partial->amount_paid);
        $this->assertSame('confirmed', $confirmedPartialPayment->status);

        $secondPayment = Payment::create([
            'loan_id' => $loan->id,
            'loan_schedule_id' => $schedule->id,
            'user_id' => $client->id,
            'amount' => 60,
            'method' => 'mvola',
            'mvola_transaction_id' => 'MVOLA-TEST-1',
            'status' => 'pending',
        ]);
        $confirmedPaidPayment = $confirmation->confirm($secondPayment);
        $paid = $schedule->refresh();

        $this->assertSame('paid', $paid->status);
        $this->assertEquals(100, $paid->amount_paid);
        $this->assertNotNull($paid->paid_at);
        $this->assertSame('confirmed', $confirmedPaidPayment->status);
        Event::assertDispatchedTimes(PaymentReceived::class, 2);

        $this->expectException(ValidationException::class);
        app(LoanService::class)->applyPayment($paid, 1);
    }

    public function test_creating_agricultural_group_loan_generates_schedule(): void
    {
        $clients = collect(range(1, 3))->map(fn (int $number) => User::create([
            'name' => 'Membre '.$number,
            'phone' => '034000000'.$number,
            'password' => 'password',
            'role' => 'client',
            'is_active' => true,
        ]));
        $group = Group::create([
            'name' => 'Groupe solidaire test',
            'status' => 'active',
        ]);
        $group->members()->attach($clients->pluck('id')->mapWithKeys(
            fn (int $id) => [$id => ['role' => 'member']]
        )->all());
        $type = LoanType::create([
            'code' => 'AGRI_TEST',
            'name' => 'Credit agricole test',
            'min_duration_months' => 3,
            'max_duration_months' => 12,
            'allowed_frequencies' => ['biweekly', 'monthly'],
            'allows_grace_period' => true,
        ]);

        $loan = app(LoanService::class)->createLoan([
            'user_id' => $clients->first()->id,
            'group_id' => $group->id,
            'loan_type_id' => $type->id,
            'amount' => 120000,
            'interest_rate' => 4,
            'duration_months' => 3,
            'repayment_frequency' => 'monthly',
            'grace_period_days' => 30,
            'first_due_date' => '2026-11-01',
        ]);

        $this->assertSame('group', $loan->solidarity_type);
        $this->assertSame($group->id, $loan->group_id);
        $this->assertCount(3, $loan->loanSchedules);
        $this->assertSame('2026-12-01', $loan->loanSchedules->first()->due_date->toDateString());
        $this->assertEquals(134400, $loan->total_repayable);
        $this->assertEquals(134400, $loan->loanSchedules->sum('amount_due'));
    }

    public function test_social_loan_amount_is_validated_against_database_limit(): void
    {
        $type = LoanType::create([
            'code' => 'SOCIAL_TEST',
            'name' => 'Credit social test',
            'allowed_frequencies' => ['monthly'],
            'max_amount' => 50000,
        ]);

        $validator = Validator::make(
            ['amount' => 50001],
            ['amount' => CreateLoanRequest::rulesFor($type->id)['amount']]
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('amount', $validator->errors()->messages());
    }

    public function test_social_loan_amount_limit_can_be_configured_without_database_value(): void
    {
        config(['loans.max_amounts.SOCIAL_URGENCE' => 25000]);
        $type = LoanType::where('code', 'SOCIAL_URGENCE')->firstOrFail();

        $validator = Validator::make(
            ['amount' => 25001],
            ['amount' => CreateLoanRequest::rulesFor($type->id)['amount']]
        );

        $this->assertSame(25000.0, $type->effective_max_amount);
        $this->assertTrue($validator->fails());
    }

    public function test_configured_interest_rate_overrides_database_default_for_new_loans(): void
    {
        config(['loans.default_interest_rates.AGR' => 6]);
        $type = LoanType::where('code', 'AGR')->firstOrFail();

        $this->assertSame(6.0, $type->effective_default_interest_rate);
    }

    public function test_client_request_is_pending_until_agent_approves_and_generates_schedule(): void
    {
        $client = User::create([
            'name' => 'Cliente test',
            'phone' => '0340000011',
            'password' => 'password',
            'role' => 'client',
            'is_active' => true,
        ]);
        $agent = User::create([
            'name' => 'Agent test',
            'phone' => '0340000012',
            'password' => 'password',
            'role' => 'agent',
            'is_active' => true,
        ]);
        $type = LoanType::where('code', 'AGR')->firstOrFail();

        $service = app(LoanService::class);
        $request = $service->submitRequest($client, [
            'loan_type_id' => $type->id,
            'amount' => 30000,
            'purpose' => 'Achat de marchandises',
            'duration_months' => 3,
            'repayment_frequency' => 'weekly',
            'grace_period_days' => 0,
        ]);

        $this->assertSame('pending', $request->status);
        $this->assertNull($request->agent_id);
        $this->assertCount(0, $request->loanSchedules);

        $approved = $service->reviewRequest($request, $agent, true);

        $this->assertSame('active', $approved->status);
        $this->assertSame($agent->id, $approved->agent_id);
        $this->assertSame($agent->id, $approved->reviewed_by);
        $this->assertNotNull($approved->approved_at);
        $this->assertNotNull($approved->contract_number);
        $this->assertGreaterThan(0, $approved->loanSchedules->count());
    }

    public function test_client_request_can_be_rejected_without_generating_a_schedule(): void
    {
        $client = User::create([
            'name' => 'Cliente test',
            'phone' => '0340000021',
            'password' => 'password',
            'role' => 'client',
            'is_active' => true,
        ]);
        $admin = User::create([
            'name' => 'Admin test',
            'phone' => '0340000022',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);
        $request = app(LoanService::class)->submitRequest($client, [
            'loan_type_id' => LoanType::where('code', 'AGR')->value('id'),
            'amount' => 30000,
            'purpose' => 'Achat de marchandises',
            'duration_months' => 3,
            'repayment_frequency' => 'weekly',
            'grace_period_days' => 0,
        ]);

        $rejected = app(LoanService::class)->reviewRequest($request, $admin, false);

        $this->assertSame('rejected', $rejected->status);
        $this->assertSame($admin->id, $rejected->reviewed_by);
        $this->assertCount(0, $rejected->loanSchedules);
    }

    public function test_fake_mvola_transaction_reference_has_no_fake_prefix(): void
    {
        $reference = app(FakeMvolaService::class)->initiatePayment('0343500003', 1000, 'Test')['serverCorrelationId'];

        $this->assertStringStartsNotWith('FAKE-', $reference);
    }

    public function test_client_can_submit_request_from_the_livewire_form(): void
    {
        $client = User::create([
            'name' => 'Client Livewire',
            'phone' => '0340000031',
            'password' => 'password',
            'role' => 'client',
            'is_active' => true,
        ]);

        Livewire::actingAs($client)
            ->test(ClientLoanRequest::class)
            ->set('loanTypeId', LoanType::where('code', 'AGR')->value('id'))
            ->set('amount', '25000')
            ->set('purpose', 'Achat de marchandises')
            ->set('durationMonths', 3)
            ->set('repaymentFrequency', 'weekly')
            ->set('gracePeriodDays', 0)
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true);

        $this->assertDatabaseHas('loans', [
            'user_id' => $client->id,
            'status' => 'pending',
            'amount' => 25000,
        ]);
    }

    public function test_public_credit_and_savings_pages_are_available(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(route('credits.index'))
            ->assertSee(route('savings.index'))
            ->assertSee('Nos services');

        $this->get('/credits')
            ->assertOk()
            ->assertSee('Credit Activite Generatrice de Revenus')
            ->assertSee('Credit Agricole et Elevage')
            ->assertSee('Credit Social / Urgence');

        $this->get('/epargne')
            ->assertOk()
            ->assertSee('Épargne');
    }
}
