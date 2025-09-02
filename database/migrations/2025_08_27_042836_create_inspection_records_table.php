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
        Schema::create('inspection_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained();
            $table->foreignId('user_id')->nullable()->constrained(); // 点検実施者
            $table->string('status')->default('依頼中'); // 依頼中, 点検済み
            $table->string('result')->nullable(); // 正常, 要確認, 異常
            $table->string('one_time_token')->unique()->nullable(); // ワンタイムURL用トークン
            $table->timestamp('token_expires_at')->nullable(); // トークン有効期限
            $table->timestamp('inspected_at')->nullable(); // 点検日時
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
        Schema::dropIfExists('inspection_records');
    }
};
