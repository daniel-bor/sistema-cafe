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
        Schema::create('parcialidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesaje_id')->constrained('pesajes');
            $table->foreignId('transporte_id')->constrained('transportes');
            $table->foreignId('transportista_id')->constrained('transportistas');
            $table->decimal('peso', 12, 2);
            $table->string('tipo_medida', 20);
            $table->datetime('fecha_recepcion')->nullable();
            $table->foreignId('estado_id')->constrained('estados');
            $table->string('codigo_qr', 50)->unique();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parcialidades');
    }
};
