<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacuum_statuses', function (Blueprint $table) {
            $table->id();
            $table->enum('state', ['standby', 'working', 'stopping', 'returning', 'charging'])->default('standby');
            $table->enum('power_mode', ['eco', 'normal', 'strong'])->default('normal');
            $table->integer('power_value')->default(200); // 150, 200, 255
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacuum_statuses');
    }
};
