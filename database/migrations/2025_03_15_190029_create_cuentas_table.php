<?php

use App\Enums\EstadoCuentaEnum;
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
            $table->tinyInteger('estado')->default(EstadoCuentaEnum::CUENTA_CREADA->value);
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
        Schema::dropIfExists('cuentas');
    }
};
