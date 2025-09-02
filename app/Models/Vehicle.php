<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'model_name',
        'vehicle_type',
        'category',
        'asset_number',
        'manufacturing_year',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'vehicle_user');
    }
}
