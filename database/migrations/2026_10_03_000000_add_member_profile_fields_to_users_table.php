<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('matricule', 30)->nullable()->unique();
            $table->string('cin', 12)->nullable()->unique();
            $table->string('profile_photo')->nullable();
            $table->string('region', 150)->nullable();
            $table->string('fokontany', 150)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['matricule']);
            $table->dropUnique(['cin']);
            $table->dropColumn(['matricule', 'cin', 'profile_photo', 'region', 'fokontany']);
        });
    }
};