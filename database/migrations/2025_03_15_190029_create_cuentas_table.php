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
        Schema::create('cuentas', function (Blueprint $table) {
            $table->id();
            $table->string('no_cuenta', 20);
            $table->foreignId('estado_id')->constrained('estados');
            $table->foreignId('agricultor_id')->constrained('agricultores');
            $table->foreignId('solicitud_id')->constrained('solicitudes_pesaje');
            $table->decimal('tolerancia', 5, 2);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuentas');
    }
};
