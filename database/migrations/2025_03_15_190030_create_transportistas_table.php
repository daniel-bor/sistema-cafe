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
        Schema::create('transportistas', function (Blueprint $table) {
            $table->id();
            $table->string('cui', 20);
            $table->string('nombre_completo', 100);
            $table->date('fecha_nacimiento');
            $table->string('tipo_licencia', 20);
            // Telefono
            $table->string('telefono', 20)->nullable();
            $table->date('fecha_vencimiento_licencia');
            $table->foreignId('agricultor_id')->constrained('agricultores');
            $table->foreignId('estado_id')->constrained('estados');
            $table->boolean('disponible')->default(true);
            $table->string('foto', 200)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transportistas');
    }
};
