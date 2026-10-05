<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->enum('status', ['pending', 'active', 'completed', 'defaulted', 'rejected'])
                ->default('pending')
                ->change();
            $table->foreignId('reviewed_by')
                ->nullable()
                ->after('approved_at')
                ->constrained('users')
                ->nullOnDelete();
            $table->dateTime('reviewed_at')->nullable()->after('reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn('reviewed_at');
            $table->enum('status', ['pending', 'active', 'completed', 'defaulted'])
                ->default('pending')
                ->change();
        });
    }
};
