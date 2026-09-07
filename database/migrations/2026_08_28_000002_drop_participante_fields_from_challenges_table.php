<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->dropForeign(['participante_id']);
            $table->dropIndex(['participante_id', 'status']);
        });

        Schema::table('challenges', function (Blueprint $table) {
            $table->dropColumn([
                'participante_id',
                'status',
                'goal_text',
                'goal_weight',
                'goal_waist',
                'goal_personal',
                'observations',
                'price',
                'payment_status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->foreignId('participante_id')->after('id')->constrained('participantes');
            $table->string('status', 30)->default('planifie')->after('end_date');
            $table->text('goal_text')->nullable()->after('status');
            $table->decimal('goal_weight', 8, 2)->nullable()->after('goal_text');
            $table->decimal('goal_waist', 8, 2)->nullable()->after('goal_weight');
            $table->text('goal_personal')->nullable()->after('goal_waist');
            $table->text('observations')->nullable()->after('goal_personal');
            $table->decimal('price', 10, 2)->after('observations');
            $table->string('payment_status', 50)->default('impaye')->after('price');
            $table->index(['participante_id', 'status']);
        });
    }
};
