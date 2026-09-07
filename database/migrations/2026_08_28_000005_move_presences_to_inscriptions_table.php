<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presences', function (Blueprint $table) {
            $table->dropForeign(['challenge_id']);
            $table->dropUnique(['challenge_id', 'attendance_date']);
            $table->dropColumn('challenge_id');
        });

        Schema::table('presences', function (Blueprint $table) {
            $table->foreignId('inscription_id')->after('id')->constrained('inscriptions');
            $table->unique(['inscription_id', 'attendance_date']);
        });
    }

    public function down(): void
    {
        Schema::table('presences', function (Blueprint $table) {
            $table->dropForeign(['inscription_id']);
            $table->dropUnique(['inscription_id', 'attendance_date']);
            $table->dropColumn('inscription_id');
        });

        Schema::table('presences', function (Blueprint $table) {
            $table->foreignId('challenge_id')->after('id')->constrained('challenges');
            $table->unique(['challenge_id', 'attendance_date']);
        });
    }
};
