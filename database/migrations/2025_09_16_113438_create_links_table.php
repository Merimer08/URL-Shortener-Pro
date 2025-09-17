<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('code', 16)->unique();
            $table->string('target_url');
            $table->unsignedInteger('max_clicks')->nullable();
            $table->unsignedInteger('click_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_access_at')->nullable();
            $table->timestamps();
            $table->softDeletes(); // si quieres recuperación de borrados
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('links');
    }
};
