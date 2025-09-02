<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('inspection_records', function (Blueprint $table) {
            // inspected_atカラムの後にis_latestカラムを追加
            $table->boolean('is_latest')->default(true)->after('inspected_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inspection_records', function (Blueprint $table) {
            //
        });
    }
};
