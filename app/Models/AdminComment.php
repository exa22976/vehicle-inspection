<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspection_record_id',
        'user_id',
        'comment',
    ];

    /**
     * このコメントを投稿した管理者を取得
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
