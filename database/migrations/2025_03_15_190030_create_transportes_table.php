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
        Schema::create('transportes', function (Blueprint $table) {
            $table->id();
            $table->string('placa', 10);
            $table->string('marca', 50);
            $table->string('color', 20);
            $table->foreignId('estado_id')->constrained('estados');
            $table->boolean('disponible')->default(true);
            $table->foreignId('agricultor_id')->constrained('agricultores');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transportes');
    }
};
