<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partager', function (Blueprint $table) {
            $table->unsignedBigInteger('id_utilisateur');
            $table->unsignedBigInteger('id_ressource');
            $table->primary(['id_utilisateur', 'id_ressource']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partager');
    }
};
