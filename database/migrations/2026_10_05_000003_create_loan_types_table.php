<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('name', 150);
            $table->string('description')->nullable();
            $table->unsignedInteger('min_duration_months')->default(1);
            $table->unsignedInteger('max_duration_months')->default(12);
            $table->json('allowed_frequencies');
            $table->boolean('allows_grace_period')->default(false);
            $table->decimal('default_interest_rate', 5, 2)->default(0);
            $table->decimal('max_amount', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('loan_types')->insert([
            [
                'code' => 'AGR',
                'name' => 'Credit Activite Generatrice de Revenus',
                'description' => 'Commerce et artisanat, remboursement hebdomadaire ou bimensuel.',
                'min_duration_months' => 3,
                'max_duration_months' => 12,
                'allowed_frequencies' => json_encode(['weekly', 'biweekly']),
                'allows_grace_period' => false,
                'default_interest_rate' => config('loans.default_interest_rates.AGR') ?? 5,
                'max_amount' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'AGRI_ELEVAGE',
                'name' => 'Credit Agricole et Elevage',
                'description' => 'Maraichage, riziculture et elevage avec possibilite de periode de grace.',
                'min_duration_months' => 3,
                'max_duration_months' => 12,
                'allowed_frequencies' => json_encode(['biweekly', 'monthly']),
                'allows_grace_period' => true,
                'default_interest_rate' => config('loans.default_interest_rates.AGRI_ELEVAGE') ?? 4,
                'max_amount' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'SOCIAL_URGENCE',
                'name' => 'Credit Social / Urgence',
                'description' => 'Education, sante et urgence familiale, faible montant.',
                'min_duration_months' => 1,
                'max_duration_months' => 12,
                'allowed_frequencies' => json_encode(['monthly']),
                'allows_grace_period' => false,
                'default_interest_rate' => config('loans.default_interest_rates.SOCIAL_URGENCE') ?? 0,
                'max_amount' => config('loans.max_amounts.SOCIAL_URGENCE'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_types');
    }
};
