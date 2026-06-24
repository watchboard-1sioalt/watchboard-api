<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('synthetiser', function (Blueprint $table) {
            $table->unsignedBigInteger('id_ressource');
            $table->unsignedBigInteger('id_synthese');
            $table->primary(['id_ressource', 'id_synthese']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('synthetiser');
    }
};
