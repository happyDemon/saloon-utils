<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('saloon_requests', function (Blueprint $table) {
            $table->string('request')->after('connector');
            $table->string('method')->after('endpoint');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('saloon_requests', fn (Blueprint $table) => $table->dropColumn(['method', 'request']));
    }
};
