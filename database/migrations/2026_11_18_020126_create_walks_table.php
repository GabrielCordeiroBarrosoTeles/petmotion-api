<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('walks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pet_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('ended_at');
            $table->unsignedInteger('duration_seconds');
            $table->unsignedInteger('distance_meters');
            $table->unsignedInteger('steps')->default(0);
            $table->decimal('temperature_c', 4, 1)->nullable();
            $table->json('points');
            $table->timestamps();

            $table->index(['pet_id', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('walks');
    }
};
