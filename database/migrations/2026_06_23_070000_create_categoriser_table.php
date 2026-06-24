<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catégoriser', function (Blueprint $table) {
            $table->unsignedBigInteger('id_tag');
            $table->unsignedBigInteger('id_ressource');
            $table->primary(['id_tag', 'id_ressource']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catégoriser');
    }
};
