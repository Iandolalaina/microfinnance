<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table "zones" : représente les zones géographiques rurales
     * (village, commune, district...) utilisées pour répartir
     * les clients entre agents ONG.
     */
    public function up(): void
    {
        Schema::create('zones', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED AUTO_INCREMENT - clé primaire
            $table->string('name', 150);              // Nom de la zone (ex: "Fokontany Ambohipo")
            $table->text('description')->nullable();  // Description libre, optionnelle
            $table->timestamps(); // created_at + updated_at (DATETIME)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zones');
    }
};
