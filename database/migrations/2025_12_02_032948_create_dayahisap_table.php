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
        Schema::create('dayahisap', function (Blueprint $table) {
            $table->id();
            $table->integer('value'); // 150, 200, 255
            $table->enum('mode', ['eco', 'normal', 'strong'])->nullable();
            $table->timestamps();
            
            // Index untuk query cepat
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dayahisap');
    }
};
