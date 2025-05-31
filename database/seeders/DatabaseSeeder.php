<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Estado;
use App\Models\Agricultor;
use App\Models\MedidaPeso;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        if (DB::connection()->getDriverName() === 'pgsql') {
            // Un search_path amplio para los seeders, ya que pueden tocar múltiples esquemas
            DB::statement("SET search_path TO agricultor_context, beneficio_context, peso_cabal_context, shared, public");
        }

        Rol::create(
            [
                'nombre' => 'AGRICULTOR',
            ],
            [
                'nombre' => 'BENEFICIO_CAFE',
            ],
            [
                'nombre' => 'PESO_CABAL',
            ],
            [
                'nombre' => 'ADMINISTRADOR',
            ]
        );
        // CREAR UN USUARIO ADMINISTRADOR POR DEFECTO
        User::factory()->create([
            'name' => 'Usuario Administrador',
            'email' => 'admin@cafe.com',
            'password' => bcrypt('12345678'),
            'rol_id' => 4, // Asignar el rol de administrador
            'activo' => true,
        ]);
        // CREAR UN USUARIO AGRICULTOR POR DEFECTO
        User::factory()->create([
            'name' => 'Usuario Agricultor',
            'email' => 'agricultor@cafe.com',
            'password' => bcrypt('12345678'),
            'rol_id' => 1, // Asignar el rol de agricultor
            'activo' => true,
        ]);
        // CREAR UN USUARIO BENEFICIO POR DEFECTO
        User::factory()->create([
            'name' => 'Usuario Beneficio',
            'email' => 'beneficio@cafe.com',
            'password' => bcrypt('12345678'),
            'rol_id' => 2, // Asignar el rol de beneficio
            'activo' => true,
        ]);
        // CREAR UN USUARIO PESO CABAL POR DEFECTO
        User::factory()->create([
            'name' => 'Usuario Peso Cabal',
            'email' => 'peso@cafe.com',
            'password' => bcrypt('12345678'),
            'rol_id' => 3, // Asignar el rol de peso cabal
            'activo' => true,
        ]);

        Agricultor::create([
            'nombre' => 'Agricultor 1',
            'apellido' => 'Apellido 1',
            'nit' => '123456789',
            'telefono' => '123456789',
            'direccion' => 'Direccion 1',
            'observaciones' => 'Observaciones 1',
            'user_id' => 2, // Asignar el usuario agricultor creado anteriormente
        ]);

        MedidaPeso::create([
            'nombre' => 'Kilogramo',
            'simbolo' => 'kg',
        ]);
    }
}
