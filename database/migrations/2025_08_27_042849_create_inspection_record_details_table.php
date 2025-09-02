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
        Schema::create('inspection_record_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_record_id')->constrained()->onDelete('cascade');
            $table->foreignId('inspection_item_id')->constrained();
            $table->string('check_result'); // 正常, 要確認, 異常
            $table->text('comment')->nullable();
            $table->string('photo_path')->nullable(); // 写真の保存パス
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
        Schema::dropIfExists('inspection_record_details');
    }
};
