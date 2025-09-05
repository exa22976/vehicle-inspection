<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspectionRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspection_request_id',
        'vehicle_id',
        'user_id',
        'status',
        'result',
        'one_time_token',
        'token_expires_at',
        'inspected_at',
        'issue_status',
        'resolved_at',
        'is_latest',
    ];

    /**
     * この点検記録が属する週次点検依頼を取得
     */
    public function inspectionRequest()
    {
        return $this->belongsTo(InspectionRequest::class);
    }

    /**
     * この点検記録の対象車両を取得
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * この点検記録の詳細（項目ごとの結果）を取得
     */
    public function details()
    {
        return $this->hasMany(InspectionRecordDetail::class);
    }

    /**
     * この点検記録に対する管理者コメントを取得
     */
    public function adminComments()
    {
        return $this->hasMany(AdminComment::class)->latest();
    }

    /**
     * この点検を実施した担当者を取得
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function inspectionRequest()
    {
        return $this->belongsTo(InspectionRequest::class);
    }
}
