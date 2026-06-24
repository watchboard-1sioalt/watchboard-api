<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('flux_rss', function (Blueprint $table) {
            $table->string('name', 150)->nullable()->after('url');
        });
    }

    public function down(): void
    {
        Schema::table('flux_rss', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};
