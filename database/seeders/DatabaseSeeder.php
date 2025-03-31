<?php

namespace Database\Seeders;

use App\Models\Agricultor;
use App\Models\Rol;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use App\Models\Estado;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        Rol::create(
            [
                'nombre' => 'AGRICULTOR',
            ],
            [
                'nombre' => 'BENEFICIO_CAFE',
            ],
            [
                'nombre' => 'PESO_CABAL',
            ]
        );

        User::factory()->create([
            'name' => 'Usuario Prueba',
            'email' => 'usuario@prueba.com',
            'password' => bcrypt('password'),
            'rol_id' => 1,
            'activo' => true,
        ]);

        Estado::create(['nombre' => 'ACTIVO', 'contexto' => 'AGRICULTOR']);
        Estado::create(['nombre' => 'INACTIVO', 'contexto' => 'AGRICULTOR']);

        Agricultor::create([
            'nombre' => 'Agricultor 1',
            'apellido' => 'Apellido 1',
            'nit' => '123456789',
            'telefono' => '123456789',
            'direccion' => 'Direccion 1',
            'observaciones' => 'Observaciones 1',
            'user_id' => 1,
        ]);
    }
}
