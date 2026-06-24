<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ressources', function (Blueprint $table) {
            $table->id('id_ressource');
            $table->string('type', 50);
            $table->text('resume')->nullable();
            $table->string('url', 255);
            $table->string('nom_original', 150)->nullable();
            $table->unsignedBigInteger('id_utilisateur');
            $table->unsignedBigInteger('id_fluxrss')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ressources');
    }
};
