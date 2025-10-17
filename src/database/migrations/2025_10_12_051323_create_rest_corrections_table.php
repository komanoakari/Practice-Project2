<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRestCorrectionsTable extends Migration
{
    public function up(): void
    {
        Schema::create('rest_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('correction_id')->constrained('attendance_corrections')->cascadeOnDelete();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rest_corrections');
    }
}
