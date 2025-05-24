<?php

use App\Enums\EstadoPesaje;
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
            $table->decimal('cantidad_total', 12, 2);
            $table->decimal('tolerancia', 5, 2)->default(5);
            $table->decimal('precio_unitario', 10, 2)->nullable();
            $table->datetime('fecha_inicio')->nullable();
            $table->datetime('fecha_cierre')->nullable();
            $table->string('observaciones')->nullable();
            $table->foreignId('cuenta_id')->nullable()->constrained('cuentas');
            $table->foreignId('agricultor_id')->constrained('agricultores');
            $table->foreignId('medida_peso_id')->constrained('medidas_peso');
            $table->tinyInteger('estado')->default(EstadoPesaje::NUEVO->value);
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
