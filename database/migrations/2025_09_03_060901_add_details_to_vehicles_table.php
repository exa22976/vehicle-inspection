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
        Schema::table('vehicles', function (Blueprint $table) {
            // ★★★★★ model_name の後に maker カラムを追加するだけに変更 ★★★★★
            $table->string('maker')->nullable()->after('model_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // ★★★★★ maker カラムを削除する処理に変更 ★★★★★
            $table->dropColumn('maker');
        });
    }
};
