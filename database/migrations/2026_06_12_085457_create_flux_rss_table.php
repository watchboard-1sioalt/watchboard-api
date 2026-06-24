<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flux_rss', function (Blueprint $table) {
            $table->id('id_fluxrss');
            $table->string('url', 255);
            $table->unsignedBigInteger('id_utilisateur');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flux_rss');
    }
};
