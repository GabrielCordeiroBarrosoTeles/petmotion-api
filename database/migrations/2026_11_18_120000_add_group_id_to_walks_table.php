<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Passeio em grupo: os registros de cada pet de um mesmo passeio compartilham o group_id. */
    public function up(): void
    {
        Schema::table('walks', function (Blueprint $table) {
            $table->uuid('group_id')->nullable()->index()->after('pet_id');
        });
    }

    public function down(): void
    {
        Schema::table('walks', function (Blueprint $table) {
            $table->dropIndex(['group_id']);
            $table->dropColumn('group_id');
        });
    }
};
