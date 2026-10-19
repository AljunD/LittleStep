<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('progress_records', function (Blueprint $table) {
            $table->date('evaluation_date_round_1')->nullable();
            $table->date('evaluation_date_round_2')->nullable();
            $table->date('evaluation_date_round_3')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('progress_records', function (Blueprint $table) {
            $table->dropColumn([
                'evaluation_date_round_1',
                'evaluation_date_round_2',
                'evaluation_date_round_3',
            ]);
        });
    }
};