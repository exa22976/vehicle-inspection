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
        Schema::table('inspection_items', function (Blueprint $table) {
            // is_requiredカラムの後にremarksカラムを追加
            $table->string('remarks')->nullable()->after('is_required');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inspection_items', function (Blueprint $table) {
            //
        });
    }
};
