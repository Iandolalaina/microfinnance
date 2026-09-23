<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table "users" : regroupe les 3 rôles de l'application
     * (admin, agent, client) grâce à la colonne "role".
     * Un client est identifié avant tout par son numéro de téléphone.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('name', 150);

            // Email optionnel : utile pour l'admin/agent, pas obligatoire pour un client rural
            $table->string('email')->nullable()->unique();

            // Numéro de téléphone = identifiant principal de connexion pour les clients
            $table->string('phone', 20)->unique();

            $table->string('password'); // mot de passe hashé (bcrypt)

            // PIN à 4-6 chiffres (hashé aussi), utilisé pour une connexion rapide mobile
            $table->string('pin')->nullable();

            // ENUM MySQL : rôle applicatif
            $table->enum('role', ['admin', 'agent', 'client'])->default('client');

            // Un client/agent appartient à une zone géographique (nullable pour l'admin)
            $table->foreignId('zone_id')
                  ->nullable()
                  ->constrained('zones')
                  ->nullOnDelete(); // si la zone est supprimée, on ne perd pas l'utilisateur

            $table->string('address')->nullable();

            $table->boolean('is_active')->default(true); // pour désactiver un compte sans le supprimer

            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
