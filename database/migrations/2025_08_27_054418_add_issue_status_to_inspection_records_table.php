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
            // resultカラムの後に2つのカラムを追加
            $table->string('issue_status')->default('未対応')->after('result'); // 未対応, 対応済み
            $table->date('resolved_at')->nullable()->after('issue_status'); // 対応完了日
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
