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
        Schema::create('inspection_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_pattern_id')->constrained()->onDelete('cascade'); // 外部キー
            $table->string('category'); // カテゴリ (車両共通/重機共通)
            $table->string('item_name'); // 項目名
            $table->integer('display_order')->default(0); // 表示順
            $table->boolean('is_required')->default(true); // 必須フラグ
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inspection_items');
    }
};
