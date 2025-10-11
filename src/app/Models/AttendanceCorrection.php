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
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
