<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAgreementRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Agreement;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Arr;
class AgreementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($projectid)
    {
        return Agreement::where('project_id', $projectid)
            ->orderBy('date')
            ->get();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {

                $projectId = $request->input('project_id');

                if (!$projectId) {
                    throw new \Exception('El campo project_id es obligatorio');
                }

                // Guardamos todos los IDs existentes del request
                $existingIds = [];

                foreach ($request->input('agreements', []) as $agreementInput) {

                    // Extraemos solo los campos válidos
                    $agreementData = Arr::only($agreementInput, [
                        'id',
                        'project_id',
                        'agreement_type_id',
                        'date',
                        'description',
                        'priority_id',
                        'ac',
                        'responsible',
                        'agreed_date',
                        'status_id',
                        'resolution',
                    ]);

                    // Aseguramos que tenga project_id y user_id
                    $agreementData['project_id'] = $projectId;
                    $agreementData['user_id'] = Auth::id();

                    // Convertimos fechas a formato válido
                    if (!empty($agreementData['date'])) {
                        $agreementData['date'] = \Carbon\Carbon::parse($agreementData['date'])->format('Y-m-d');
                    }

                    if (!empty($agreementData['agreed_date'])) {
                        $agreementData['agreed_date'] = \Carbon\Carbon::parse($agreementData['agreed_date'])->format('Y-m-d');
                    }

                    // Update or create
                    $agreement = Agreement::updateOrCreate(
                        ['id' => $agreementData['id'] ?? null],
                        $agreementData
                    );

                    $existingIds[] = $agreement->id;
                }

                // 🔥 Eliminamos los acuerdos que ya no están en la lista
                Agreement::where('project_id', $projectId)
                    ->whereNotIn('id', $existingIds)
                    ->delete();
            });

            return response()->json(['message' => 'Acuerdos guardados correctamente']);
        } catch (\Throwable $e) {
            Log::error("Error al guardar acuerdos: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Error al guardar acuerdos',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Agreement $agreement)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Agreement $agreement)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAgreementRequest $request, Agreement $agreement)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $deleted = Agreement::destroy((int)$id);

            if ($deleted) {
                return response()->json(['message' => 'Acuerdo eliminado correctamente']);
            } else {
                return response()->json(['message' => 'No se encontró el Acuerdo'], 404);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }
}
