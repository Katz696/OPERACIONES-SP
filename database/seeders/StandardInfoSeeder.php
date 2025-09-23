<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StandardInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Desactivar restricciones para truncar
        Schema::disableForeignKeyConstraints();

        DB::table('type_notes')->truncate();
        DB::table('impacts')->truncate();
        DB::table('statuses')->truncate();
        DB::table('substatuses')->truncate();
        DB::table('priorities')->truncate();
        DB::table('sprints')->truncate();

        Schema::enableForeignKeyConstraints();

        // Type Notes
        DB::table('type_notes')->insert([
            ['type' => 'Oportunidad'],
            ['type' => 'Riesgo'],
            ['type' => 'Bloqueo'],
            ['type' => 'Información'],
            ['type' => 'Cambio'],
        ]);

        // Impacts
        DB::table('impacts')->insert([
            ['impact' => 'Bajo'],
            ['impact' => 'Medio'],
            ['impact' => 'Alto'],
            ['impact' => 'Crítico'],
        ]);

        // Statuses
        DB::table('statuses')->insert([
            ['status' => 'No iniciado',     'color' => '#6c757d'], // gris
            ['status' => 'En progreso',     'color' => '#0d6efd'], // azul
            ['status' => 'En revisión',     'color' => '#ffc107'], // amarillo
            ['status' => 'Completado',      'color' => '#198754'], // verde
            ['status' => 'Cancelado',       'color' => '#dc3545'], // rojo
            ['status' => 'Aplazado',        'color' => '#fd7e14'], // naranja
        ]);

        // Statuses
        DB::table('substatuses')->insert([
            ['substatus' => 'Nuevo',     'color' => '#6c757d'], // gris
            ['substatus' => 'Asignado',     'color' => '#0d6efd'], // azul
            ['substatus' => 'Cancelado',     'color' => '#ffc107'], // amarillo
            ['substatus' => 'Resuelta',      'color' => '#198754'], // verde
        ]);
        // Priorities
        DB::table('priorities')->insert([
            ['priority' => 'Baja',     'color' => '#6c757d'], // gris
            ['priority' => 'Media',    'color' => '#0d6efd'], // azul
            ['priority' => 'Alta',     'color' => '#ffc107'], // amarillo
            ['priority' => 'Urgente',  'color' => '#dc3545'], // rojo
        ]);

        // Sprints
        DB::table('sprints')->insert([
            ['sprint' => 'Sprint 1'],
            ['sprint' => 'Sprint 2'],
            ['sprint' => 'Sprint 3'],
            ['sprint' => 'Sprint 4'],
            ['sprint' => 'Sprint 5'],
            ['sprint' => 'Sprint 6'],
        ]);
    }
}
