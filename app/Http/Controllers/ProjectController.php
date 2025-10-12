<?php

namespace App\Http\Controllers;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{Project, Phase, Delivery, Activity, Status, Substatus, Priority, Sprint, User};
use Illuminate\Support\Facades\Log;
class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function showTree(Request $request)
    {
        // Validar que se envíe el ID del proyecto
        $request->validate([
            'id' => 'required|integer|exists:projects,id',
        ]);
        $id = $request->input('id');
        $project = Project::with([
            'customer',
            'user:id,name',
            'status:id,status,color',
            'phases.substatus',
            'phases.deliveries.activities',
            'phases.deliveries.status',
            'phases.deliveries.substatus',
            'phases.deliveries.priority',
            'phases.deliveries.sprint',
            'phases.deliveries.user',
            'phases.deliveries.activities.status',
            'phases.deliveries.activities.substatus',
            'phases.deliveries.activities.priority',
            'phases.deliveries.activities.user'
        ])->findOrFail($id);

        // Extra: catálogos
        $statuses = Status::select('id', 'status', 'color')->get();
        $substatuses = Substatus::select('id', 'substatus')->get();
        $priorities = Priority::select('id', 'priority', 'color')->get();
        $sprints = Sprint::select('id', 'sprint')->get();
        $users = User::select('id', 'name')->get();

        return response()->json([
            'project' => [
                'data' => $project->only([
                    'id',
                    'customer_id',
                    'user_id',
                    'index',
                    'title',
                    'description',
                    'status_id',
                    'days',
                    'comments',
                    'percentage',
                    'percentage_planned',
                    'percentage_progress',
                    'start_date',
                    'end_date',
                    'real_end_date',
                    'restriction_start_date',
                    'restriction_end_date'
                ]),
                'phases' => $project->phases->map(function ($phase) {
                    return [
                        'data' => $phase->only([
                            'id',
                            'project_id',
                            'status_id',
                            'substatus_id',
                            'title',
                            'index',
                            'days',
                            'comments',
                            'percentage',
                            'percentage_planned',
                            'percentage_progress',
                            'start_date',
                            'end_date',
                            'real_end_date',
                            'restriction_start_date',
                            'restriction_end_date',
                            'depend_me',
                            'i_depend'
                        ]),
                        'deliveries' => $phase->deliveries->map(function ($delivery) {
                            return [
                                'data' => $delivery->only([
                                    'id',
                                    'project_id',
                                    'phase_id',
                                    'status_id',
                                    'substatus_id',
                                    'priority_id',
                                    'user_id',
                                    'sprint_id',
                                    'index',
                                    'title',
                                    'description',
                                    'days',
                                    'comments',
                                    'percentage',
                                    'percentage_planned',
                                    'percentage_progress',
                                    'start_date',
                                    'end_date',
                                    'real_end_date',
                                    'restriction_start_date',
                                    'restriction_end_date',
                                    'depend_me',
                                    'i_depend'
                                ]),
                                'activities' => $delivery->activities->map(function ($activity) {
                                    return [
                                        'data' => $activity->only([
                                            'id',
                                            'delivery_id',
                                            'status_id',
                                            'substatus_id',
                                            'priority_id',
                                            'user_id',
                                            'index',
                                            'title',
                                            'percentage',
                                            'percentage_planned',
                                            'percentage_progress',
                                            'days',
                                            'comments',
                                            'start_date',
                                            'end_date',
                                            'real_end_date',
                                            'restriction_start_date',
                                            'restriction_end_date',
                                            'depend_me',
                                            'i_depend'
                                        ])
                                    ];
                                })
                            ];
                        })
                    ];
                })
            ],
            'customer' => $project->customer,
            'statuses' => $statuses,
            'substatuses' => $substatuses,
            'priorities' => $priorities,
            'users' => $users,
            'sprints' => $sprints,
        ]);
    }

    public function updateTree(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                // --- PROJECT ---
                $projectData = Arr::only($request->input('project.data', []), [
                    'id',
                    'customer_id',
                    'user_id',
                    'index',
                    'title',
                    'percentage',
                    'percentage_planned',
                    'percentage_progress',
                    'description',
                    'status_id',
                    'days',
                    'comments',
                    'start_date',
                    'end_date',
                    'real_end_date',
                    'restriction_start_date',
                    'restriction_end_date',
                ]);

                $project = Project::updateOrCreate(
                    ['id' => $projectData['id'] ?? null],
                    $projectData
                );

                $existingPhaseIds = [];

                // --- PHASES ---
                foreach ($request->input('project.phases', []) as $phaseInput) {
                    $phaseData = Arr::only($phaseInput['data'] ?? [], [
                        'id',
                        'project_id',
                        'status_id',
                        'substatus_id',
                        'title',
                        'percentage',
                        'percentage_planned',
                        'percentage_progress',
                        'index',
                        'days',
                        'comments',
                        'start_date',
                        'end_date',
                        'real_end_date',
                        'restriction_start_date',
                        'restriction_end_date',
                        'depend_me',
                        'i_depend'
                    ]);

                    $phase = Phase::updateOrCreate(
                        ['id' => $phaseData['id'] ?? null],
                        array_merge($phaseData, ['project_id' => $project->id])
                    );

                    $existingPhaseIds[] = $phase->id;
                    $existingDeliveryIds = [];

                    // --- DELIVERIES ---
                    foreach ($phaseInput['deliveries'] ?? [] as $deliveryInput) {
                        $deliveryData = Arr::only($deliveryInput['data'] ?? [], [
                            'id',
                            'project_id',
                            'phase_id',
                            'status_id',
                            'substatus_id',
                            'priority_id',
                            'user_id',
                            'sprint_id',
                            'index',
                            'title',
                            'percentage',
                            'percentage_planned',
                            'percentage_progress',
                            'description',
                            'days',
                            'comments',
                            'start_date',
                            'end_date',
                            'real_end_date',
                            'restriction_start_date',
                            'restriction_end_date',
                            'depend_me',
                            'i_depend'
                        ]);

                        $delivery = Delivery::updateOrCreate(
                            ['id' => $deliveryData['id'] ?? null],
                            array_merge($deliveryData, [
                                'project_id' => $project->id,
                                'phase_id'   => $phase->id,
                            ])
                        );

                        $existingDeliveryIds[] = $delivery->id;
                        $existingActivityIds = [];

                        // --- ACTIVITIES ---
                        foreach ($deliveryInput['activities'] ?? [] as $activityInput) {
                            $activityData = Arr::only($activityInput['data'] ?? [], [
                                'id',
                                'delivery_id',
                                'status_id',
                                'substatus_id',
                                'priority_id',
                                'user_id',
                                'index',
                                'title',
                                'percentage',
                                'percentage_planned',
                                'percentage_progress',
                                'days',
                                'comments',
                                'start_date',
                                'end_date',
                                'real_end_date',
                                'restriction_start_date',
                                'restriction_end_date',
                                'activity_id',
                                'depend_me',
                                'i_depend'
                            ]);

                            $activity = Activity::updateOrCreate(
                                ['id' => $activityData['id'] ?? null],
                                array_merge($activityData, ['delivery_id' => $delivery->id])
                            );

                            $existingActivityIds[] = $activity->id;
                        }

                        // eliminar actividades que ya no están
                        $delivery->activities()->whereNotIn('id', $existingActivityIds)->delete();
                    }

                    // eliminar entregas que ya no están
                    $phase->deliveries()->whereNotIn('id', $existingDeliveryIds)->delete();
                }

                // eliminar fases que ya no están
                $project->phases()->whereNotIn('id', $existingPhaseIds)->delete();
            });

            return response()->json(['message' => 'Proyecto actualizado correctamente']);
        } catch (\Throwable $e) {
            // loguea en laravel.log
            Log::error("updateTree error: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            // responde con el error para depuración
            return response()->json([
                'message' => 'Error en updateTree',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
