<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SpaceType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SpaceTypeController extends Controller
{
    public function index(): JsonResponse
    {
        $types = SpaceType::withCount('spaces')->get();
        return $this->successResponse($types, 'Listado de tipos de espacio');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'unique:space_types,name'],
            'description' => ['nullable', 'string'],
        ]);

        $spaceType = SpaceType::create($validated);
        return $this->successResponse($spaceType, 'Tipo de espacio creado correctamente', 201);
    }

    public function show(SpaceType $spaceType): JsonResponse
    {
        return $this->successResponse($spaceType->load('spaces'), 'Detalle del tipo de espacio');
    }

    public function update(Request $request, SpaceType $spaceType): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'unique:space_types,name,' . $spaceType->id],
            'description' => ['nullable', 'string'],
        ]);

        $spaceType->update($validated);
        return $this->successResponse($spaceType, 'Tipo de espacio actualizado correctamente');
    }

    public function destroy(SpaceType $spaceType): JsonResponse
    {
        if ($spaceType->spaces()->exists()) {
            return $this->errorResponse('No se puede eliminar un tipo de espacio que tiene espacios asociados.', 422);
        }

        $spaceType->delete();
        return $this->successResponse(null, 'Tipo de espacio eliminado correctamente');
    }
}
