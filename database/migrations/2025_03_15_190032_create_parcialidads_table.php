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
            $table->decimal('peso_bascula', 12, 2)->nullable();
            $table->string('tipo_medida', 20)->nullable(); //Eliminar
            $table->datetime('fecha_envio')->nullable();
            $table->datetime('fecha_recepcion')->nullable();
            $table->foreignId('estado_id')->constrained('estados'); // Reemplazado por enum
            $table->string('codigo_qr', 50)->nullable(); //Eliminar
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
