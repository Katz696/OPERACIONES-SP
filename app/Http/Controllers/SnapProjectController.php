<?php
// app/Http/Controllers/SnapProjectController.php
namespace App\Http\Controllers;

use App\Models\SnapProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SnapProjectController extends Controller
{
    public function index($projectId)
    {
        return SnapProject::where('project_id', $projectId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function store(Request $request)
    {
        $snap = SnapProject::create([
            'project_id' => $request->project_id,
            'user_id' => Auth::id(),
            'label' => $request->label,
            'percentage' => $request->percentage ?? 0,
            'percentage_planned' => $request->percentage_planned ?? 0,
            'percentage_progress' => $request->percentage_progress ?? 0,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'data' => $request->data, // el JSON completo del proyecto
        ]);
        $resp = SnapProject::where('id', $snap->id)
            ->with('user')
            ->get();
        return response()->json($resp);
    }
    public function destroy($id)
    {
        try {
            $deleted = SnapProject::destroy((int)$id);

            if ($deleted) {
                return response()->json(['message' => 'Snap eliminado correctamente']);
            } else {
                return response()->json(['message' => 'No se encontró el Snap'], 404);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }
}
