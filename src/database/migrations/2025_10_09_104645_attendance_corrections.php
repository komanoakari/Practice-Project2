<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AttendanceCorrections extends Migration
{
    public function up()
    {
        Schema::create('attendance_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained()->cascadeOnDelete();
            $table->timestamp('applied_at');
            $table->string('status')->default('未申請');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_corrections');
    }
}
