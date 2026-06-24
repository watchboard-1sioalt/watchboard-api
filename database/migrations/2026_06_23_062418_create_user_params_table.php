<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('userParams', function (Blueprint $table) {
            $table->unsignedBigInteger('id_utilisateur');
            $table->unsignedBigInteger('id_parametre');
            $table->string('paramValue', 255)->nullable();
            $table->primary(['id_utilisateur', 'id_parametre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('userParams');
    }
};
