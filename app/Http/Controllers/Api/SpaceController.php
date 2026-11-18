<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSpaceRequest;
use App\Http\Requests\UpdateSpaceRequest;
use App\Models\Space;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SpaceController extends Controller
{
    /**
     * RF01: Catálogo de espacios disponibles con filtros por tipo, capacidad y rango de fechas.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Space::with('spaceType')->active();

        if ($request->filled('space_type_id')) {
            $query->where('space_type_id', $request->space_type_id);
        }

        if ($request->filled('capacity')) {
            $query->where('capacity', '>=', (int) $request->capacity);
        }

        if ($request->filled(['start_time', 'end_time'])) {
            $start = $request->start_time;
            $end = $request->end_time;

            $query->whereDoesntHave('reservations', function ($q) use ($start, $end) {
                $q->overlapping($start, $end);
            });
        }

        $spaces = $query->get();

        return $this->successResponse($spaces, 'Catálogo de espacios disponibles');
    }

    public function store(StoreSpaceRequest $request): JsonResponse
    {
        $space = Space::create($request->validated());
        return $this->successResponse($space->load('spaceType'), 'Espacio creado correctamente', 201);
    }

    public function show(Space $space): JsonResponse
    {
        return $this->successResponse($space->load('spaceType'), 'Detalle del espacio');
    }

    public function update(UpdateSpaceRequest $request, Space $space): JsonResponse
    {
        $space->update($request->validated());
        return $this->successResponse($space->load('spaceType'), 'Espacio actualizado correctamente');
    }

    public function destroy(Space $space): JsonResponse
    {
        $space->delete();
        return $this->successResponse(null, 'Espacio eliminado correctamente');
    }

    /**
     * RF07: Ver disponibilidad y ocupación de todos los espacios por día (Staff / Admin).
     */
    public function dailyOccupancy(Request $request): JsonResponse
    {
        $date = $request->input('date', now()->toDateString());

        $spaces = Space::with(['spaceType', 'reservations' => function ($q) use ($date) {
            $q->whereDate('start_time', $date)
              ->whereIn('status', ['pending', 'confirmed', 'completed'])
              ->with('user:id,name,email');
        }])->get();

        return $this->successResponse([
            'date' => $date,
            'spaces' => $spaces,
        ], 'Ocupación diaria de espacios');
    }
}
