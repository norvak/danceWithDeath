<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('email')->unique();
            $table->date('appointment_date');
            $table->time('appointment_time', 0);
            $table->timestamps();

            $table->unique(
                ['appointment_date', 'appointment_time'],
                'appointments_date_time_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
