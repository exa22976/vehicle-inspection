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
            $table->timestamp('send_at')->nullable()->after('target_week_start'); // 通知送信日時
        });
    }

    // downメソッドも念のため記述しておきます
    public function down()
    {
        Schema::table('inspection_requests', function (Blueprint $table) {
            $table->dropColumn('send_at');
        });
    }
};
