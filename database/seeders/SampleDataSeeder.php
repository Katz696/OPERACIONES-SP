<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\Project;
use App\Models\User;
use App\Models\Phase;
use App\Models\Delivery;
use App\Models\Activity;
use App\Models\Stakeholder;
use App\Models\Requirement;
use Illuminate\Support\Facades\DB;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        // Crear 3 clientes
        $customers = Customer::factory()->count(3)->create();

        // Crear 1 usuario (si no tienes factory aún, puedes hardcodear)
        $user = User::first() ?? User::factory()->create();

        foreach ($customers as $customer) {
            // Crear proyecto por cliente
            $project = Project::create([
                'customer_id' => $customer->id,
                'user_id' => $user->id,
                'index' => 1,
                'title' => 'Proyecto de ' . $customer->name,
                'status_id' => DB::table('statuses')->where('status', 'No iniciado')->value('id'),
                'days' => 30,
                'start_date' => now(),
            ]);

            // Crear fases
            $phase = Phase::create([
                'project_id' => $project->id,
                'status_id' => DB::table('statuses')->where('status', 'No iniciado')->value('id'),
                'substatus_id' => DB::table('substatuses')->where('substatus', 'Por Iniciar')->value('id'),
                'title' => 'Fase Inicial',
                'index' => '1.1',
                'days' => 10,
            ]);

            // Crear entregas
            $delivery = Delivery::create([
                'project_id' => $project->id,
                'phase_id' => $phase->id,
                'status_id' => DB::table('statuses')->where('status', 'No iniciado')->value('id'),
                'substatus_id' => DB::table('substatuses')->where('substatus', 'Por Iniciar')->value('id'),
                'priority_id' => DB::table('priorities')->where('priority', 'Media')->value('id'),
                'user_id' => $user->id,
                'sprint_id' => DB::table('sprints')->first()->id,
                'index' => '1.1.1',
                'title' => 'Entrega 1',
            ]);

            // Crear actividad
            Activity::create([
                'delivery_id' => $delivery->id,
                'status_id' => DB::table('statuses')->where('status', 'No iniciado')->value('id'),
                'substatus_id' => DB::table('substatuses')->where('substatus', 'Por Iniciar')->value('id'),
                'priority_id' => DB::table('priorities')->where('priority', 'Alta')->value('id'),
                'user_id' => $user->id,
                'title' => 'Actividad 1',
                'index' => '1.1..',
                'percentage' => 0.00,
                'days' => 5,
            ]);

            // Crear stakeholders
            $stakeholder = Stakeholder::create([
                'project_id' => $project->id,
            ]);

            // Crear requisito
            Requirement::create([
                'project_id' => $project->id,
                'stakeholder_id' => $stakeholder->id,
                'description' => 'Requisito funcional 1',
            ]);
        }
    }
}
