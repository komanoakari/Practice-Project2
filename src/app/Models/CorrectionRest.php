<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectionRest extends Model
{
    use HasFactory;

    protected $fillable = [
        'correction_id',
        'start_time',
        'end_time',
    ];

    public function correction()
    {
        return $this->belongsTo(AttendanceCorrection::class, 'correction_id');
    }
}
