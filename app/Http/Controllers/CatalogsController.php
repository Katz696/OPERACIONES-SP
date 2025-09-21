<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class CatalogsController extends Controller
{
    public function getProjects()
    {
        try {
            $projects = Project::with('status')->get(); // Ejemplo con relaciones

            return response()->json([
                'status' => 'success',
                'data' => $projects
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener los proyectos',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getUsers()
    {
        try {
            $users = User::get(); // Ejemplo con relaciones

            return response()->json([
                'status' => 'success',
                'data' => $users
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener los proyectos',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getCustomers()
    {
        try {
            $users = Customer::get(); // Ejemplo con relaciones

            return response()->json([
                'status' => 'success',
                'data' => $users
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener los proyectos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateProjects(Request $request)
    {
        $projects = collect($request->all());

        // IDs entrantes
        $incomingIds = $projects->pluck('id')->filter()->all();

        // Eliminar los que no estén
        Project::whereNotIn('id', $incomingIds)->delete();

        foreach ($projects as $data) {
            $filtered = collect($data)->only((new Project)->getFillable())->toArray();

            if (!empty($data['id'])) {
                $project = Project::find($data['id']);
                if ($project) {
                    $project->fill($filtered);
                    $project->save();
                }
            } else {
                Project::create($filtered);
            }
        }

        return response()->json(['status' => 'ok']);
    }
    public function updateUsers(Request $request)
    {
        $usersData = collect($request->all());

        // IDs que vienen desde el frontend (solo los que ya existen)
        $incomingIds = $usersData->pluck('id')->filter()->all();

        // Eliminar usuarios que ya no están en el listado
        User::whereNotIn('id', $incomingIds)->delete();

        foreach ($usersData as $data) {
            $filtered = collect($data)->only(['name', 'email'])->toArray();

            if (!empty($data['id'])) {
                // Actualizar usuario existente
                User::where('id', $data['id'])->update($filtered);
            } else {
                // Crear nuevo usuario con password por defecto
                $filtered['password'] = Hash::make('password');
                User::create($filtered);
            }
        }

        return response()->json(['status' => 'ok']);
    }
    public function updateCustomers(Request $request)
    {
        $customersData = collect($request->all());

        // IDs que vienen desde el frontend (solo los que ya existen)
        $incomingIds = $customersData->pluck('id')->filter()->all();

        // Eliminar usuarios que ya no están en el listado
        Customer::whereNotIn('id', $incomingIds)->delete();

        foreach ($customersData as $data) {
            $filtered = collect($data)->only(['name', 'email','phone','address'])->toArray();

            if (!empty($data['id'])) {
                // Actualizar usuario existente
                Customer::where('id', $data['id'])->update($filtered);
            } else {
                // Crear nuevo usuario con password por defecto
                Customer::create($filtered);
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
