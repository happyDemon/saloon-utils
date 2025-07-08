<?php

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
        Schema::create('saloon_requests', function (Blueprint $table) {
            $table->id();
            $table->string('connector');
            $table->string('endpoint');
            $table->json('request_headers')->nullable();
            $table->json('request_query')->nullable();
            $table->longText('request_body')->nullable();
            $table->json('response_headers')->nullable();
            $table->longText('response_body')->nullable();
            $table->unsignedTinyInteger('status_code')->nullable();
            $table->timestamps();
            $table->unsignedInteger('completed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saloon_requests');
    }
};
