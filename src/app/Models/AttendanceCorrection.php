<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrection extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'applied_at',
        'status',
        'start_time',
        'end_time',
        'remarks',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function restCorrections()
    {
        return $this->hasMany(RestCorrection::class, 'correction_id');
    }
}
