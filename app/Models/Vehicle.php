<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // SoftDeletes を使用

class Vehicle extends Model
{
    use HasFactory, SoftDeletes; // SoftDeletes を使用

    // ★★★★★ 実際のカラム名に合わせて全面的に修正 ★★★★★
    protected $fillable = [
        'model_name',
        'maker', // 新規追加
        'vehicle_type',
        'category',
        'asset_number',
        'manufacturing_year',
    ];

    public function users()
    {
        // ★★★★★ 中間テーブルの名前を明示的に指定 ★★★★★
        return $this->belongsToMany(User::class, 'vehicle_user');
    }
}
