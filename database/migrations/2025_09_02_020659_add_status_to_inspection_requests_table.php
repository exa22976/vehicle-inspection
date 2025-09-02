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
    public function up()
    {
        Schema::table('inspection_requests', function (Blueprint $table) {
            // target_week_start の後ろに status 列を追加
            $table->string('status')->default('scheduled')->after('target_week_start');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inspection_requests', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
