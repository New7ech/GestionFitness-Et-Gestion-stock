<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mesures', function (Blueprint $table) {
            $table->dropForeign(['challenge_id']);
            $table->dropIndex(['challenge_id', 'measured_at']);
            $table->dropColumn('challenge_id');
        });

        Schema::table('mesures', function (Blueprint $table) {
            $table->foreignId('inscription_id')->after('id')->constrained('inscriptions');
            $table->index(['inscription_id', 'measured_at']);
        });
    }

    public function down(): void
    {
        Schema::table('mesures', function (Blueprint $table) {
            $table->dropForeign(['inscription_id']);
            $table->dropIndex(['inscription_id', 'measured_at']);
            $table->dropColumn('inscription_id');
        });

        Schema::table('mesures', function (Blueprint $table) {
            $table->foreignId('challenge_id')->after('id')->constrained('challenges');
            $table->index(['challenge_id', 'measured_at']);
        });
    }
};
