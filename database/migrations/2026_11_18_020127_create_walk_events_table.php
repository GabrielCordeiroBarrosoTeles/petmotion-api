<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('walk_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('walk_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['pee', 'poop', 'water']);
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->timestamp('occurred_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('walk_events');
    }
};
