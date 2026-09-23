<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table "announcements" : annonces/informations publiées par
     * l'ONG (agent ou admin), affichées dans le fil d'actualité client
     * et éventuellement relayées par SMS.
     */
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();

            $table->string('title', 200);
            $table->text('content');

            // Si null => annonce nationale/générale, sinon ciblée sur une zone précise
            $table->foreignId('zone_id')
                  ->nullable()
                  ->constrained('zones')
                  ->nullOnDelete();

            // Qui a publié (admin ou agent)
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->boolean('send_sms')->default(false); // faut-il aussi l'envoyer par SMS ?

            $table->dateTime('published_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
