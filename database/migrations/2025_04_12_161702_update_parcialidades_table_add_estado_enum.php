<?php

use App\Enums\EstadoParcialidad;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Reemplaza el campo estado_id (foreign key) con un campo estado de tipo string
     * para usar con el enum EstadoParcialidad
     */
    public function up(): void
    {
        Schema::table('parcialidades', function (Blueprint $table) {
            // Si existe la columna estado_id, primero la eliminamos
            if (Schema::hasColumn('parcialidades', 'estado_id')) {
                // Eliminar la clave foránea si existe
                $table->dropForeign(['estado_id']);
                $table->dropColumn('estado_id');
            }

            // Añadir la nueva columna estado para el enum
            if (!Schema::hasColumn('parcialidades', 'estado')) {
                $table->tinyInteger('estado')->default(EstadoParcialidad::PENDIENTE->value);
            }
        });
    }

    /**
     * Reverse the migrations.
     * Regresa el estado anterior eliminando el campo estado y añadiendo de nuevo estado_id
     */
    public function down(): void
    {
        Schema::table('parcialidades', function (Blueprint $table) {
            // Si existe la columna estado, la eliminamos
            if (Schema::hasColumn('parcialidades', 'estado')) {
                $table->dropColumn('estado');
            }

            // Añadir de nuevo la columna estado_id relacionada con la tabla estados
            if (!Schema::hasColumn('parcialidades', 'estado_id')) {
                $table->unsignedBigInteger('estado_id')->nullable();
                $table->foreign('estado_id')->references('id')->on('estados');
            }
        });
    }
};
