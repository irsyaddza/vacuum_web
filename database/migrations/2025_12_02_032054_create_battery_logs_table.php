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
        Schema::create('battery_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('battery_percent'); // 0-100
            $table->decimal('battery_voltage', 5, 2)->nullable(); // e.g. 12.50V
            $table->string('estimated_time')->nullable(); // e.g "3 hours 45 minutes"
            $table->timestamps();
            
            // Index untuk query history lebih cepat
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('battery_logs');
    }
};
