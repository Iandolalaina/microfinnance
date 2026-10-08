<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MemberRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_receives_a_matricule_after_registration_and_can_sign_in_with_it(): void
    {
        Storage::fake('public');

        $response = $this->post(route('register'), $this->registrationData());
        $response->assertRedirect('/client/dashboard');

        $member = User::where('phone', '0340000000')->firstOrFail();

        $this->assertAuthenticatedAs($member);
        $this->assertNull($member->email);
        $this->assertSame('client', $member->role);
        $this->assertSame('123456789012', $member->cin);
        $this->assertMatchesRegularExpression('/^MTS-\d{4}-\d{6}$/', $member->matricule);
        $response->assertSessionHas('member_matricule', $member->matricule);
        Storage::disk('public')->assertExists($member->profile_photo);
        $this->get('/client/dashboard')
            ->assertOk()
            ->assertSee($member->matricule)
            ->assertSee(Storage::disk('public')->url($member->profile_photo), false);

        $this->post(route('logout'))->assertRedirect('/login');
        $this->post('/login', [
            'login' => $member->matricule,
            'password' => 'secret-password',
        ])->assertRedirect('/client/dashboard');

        $this->assertAuthenticatedAs($member);

        $this->post(route('logout'));
        $this->post('/login', [
            'login' => '0340000000',
            'password' => 'secret-password',
        ])->assertRedirect('/client/dashboard');
        $this->assertAuthenticatedAs($member);

        $this->post(route('logout'));
        $member->update(['is_active' => false]);

        $this->post('/login', [
            'login' => $member->matricule,
            'password' => 'secret-password',
        ])->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_registration_rejects_cin_that_is_not_exactly_twelve_digits(): void
    {
        Storage::fake('public');

        $this->post(route('register'), array_merge($this->registrationData(), [
            'cin' => '12345678901',
        ]))
            ->assertSessionHasErrors('cin');

        $this->assertDatabaseCount('users', 0);
    }

    private function registrationData(): array
    {
        return [
            'name' => 'Membre Test',
            'phone' => '0340000000',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'cin' => '123456789012',
            'email' => '',
            'region' => 'Atsinanana',
            'fokontany' => 'Ankirihiry',
            'profile_photo' => UploadedFile::fake()->image('member.jpg'),
        ];
    }
}
