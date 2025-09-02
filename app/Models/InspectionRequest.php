<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspection_pattern_id',
        'target_week_start',
        'remarks',
    ];

    public function records()
    {
        return $this->hasMany(InspectionRecord::class);
    }

    public function pattern()
    {
        return $this->belongsTo(InspectionPattern::class, 'inspection_pattern_id');
    }
}
