<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appartenir', function (Blueprint $table) {
            $table->unsignedBigInteger('id_tag');
            $table->unsignedBigInteger('id_fluxrss');
            $table->primary(['id_tag', 'id_fluxrss']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appartenir');
    }
};
