<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspectionRecordDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspection_record_id',
        'inspection_item_id',
        'check_result',
        'comment',
        'photo_path',
    ];

    /**
     * この詳細が属する点検項目を取得
     */
    public function item()
    {
        return $this->belongsTo(InspectionItem::class, 'inspection_item_id');
    }
}
