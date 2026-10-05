<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->foreignId('loan_type_id')->nullable()->after('agent_id')->constrained('loan_types')->nullOnDelete();
            $table->foreignId('group_id')->nullable()->after('loan_type_id')->constrained('groups')->nullOnDelete();
            $table->enum('solidarity_type', ['individual', 'group'])->default('individual')->after('group_id');
            $table->enum('repayment_frequency', ['weekly', 'biweekly', 'monthly'])->default('monthly')->after('duration_months');
            $table->unsignedInteger('grace_period_days')->default(0)->after('repayment_frequency');
            $table->decimal('total_repayable', 12, 2)->default(0)->after('interest_rate');
            $table->string('purpose')->nullable()->after('total_repayable');
            $table->string('contract_number', 80)->nullable()->unique()->after('purpose');
            $table->date('first_due_date')->nullable()->after('contract_number');
            $table->dateTime('approved_at')->nullable()->after('disbursed_at');
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('loan_type_id');
            $table->dropConstrainedForeignId('group_id');
            $table->dropColumn([
                'solidarity_type',
                'repayment_frequency',
                'grace_period_days',
                'total_repayable',
                'purpose',
                'contract_number',
                'first_due_date',
                'approved_at',
            ]);
        });
    }
};
