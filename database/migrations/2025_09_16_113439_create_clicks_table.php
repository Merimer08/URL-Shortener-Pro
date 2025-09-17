<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('link_id')->constrained()->cascadeOnDelete();
            $table->timestamp('clicked_at')->useCurrent();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('browser', 50)->nullable();
            $table->string('country', 2)->nullable(); // opcional si luego añades geoip
            $table->timestamps();

            $table->index(['link_id', 'clicked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clicks');
    }
};
