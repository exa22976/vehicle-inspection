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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('model_name'); // 型式
            $table->string('vehicle_type'); // 車両種別
            $table->string('category'); // カテゴリ (車両/重機)
            $table->unsignedBigInteger('asset_number')->nullable(); // 管理番号 (空を許容)
            $table->year('manufacturing_year')->nullable(); // 製造年
            $table->timestamps();
            $table->softDeletes(); // 論理削除用
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vehicles');
    }
};
