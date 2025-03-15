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
        Schema::create('pesajes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medida_peso_id')->constrained('medidas_peso');
            $table->decimal('peso_total', 12, 2);
            $table->foreignId('estado_id')->constrained('estados');
            $table->foreignId('solicitud_id')->constrained('solicitudes_pesaje');
            $table->foreignId('cuenta_id')->constrained('cuentas');
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->datetime('fecha_inicio')->nullable();
            $table->datetime('fecha_cierre')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesajes');
    }
};
