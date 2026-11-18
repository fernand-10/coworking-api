<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Models\Reservation;
use App\Models\Space;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    /**
     * RF03: Historial de reservas propias del cliente autenticado.
     */
    public function myReservations(Request $request): JsonResponse
    {
        $reservations = $request->user()
            ->reservations()
            ->with('space.spaceType')
            ->orderByDesc('start_time')
            ->get();

        return $this->successResponse($reservations, 'Historial de reservas del usuario');
    }

    /**
     * RF02: Crear reserva aplicando reglas de negocio (solapamiento y límite de 2 reservas activas futuras).
     */
    public function store(StoreReservationRequest $request): JsonResponse
    {
        $user = $request->user();
        $space = Space::findOrFail($request->space_id);

        if (!$space->is_active) {
            return $this->errorResponse('El espacio seleccionado no se encuentra activo.', 422);
        }

        // Regla de negocio 1: Verificar solapamiento horario en ese espacio
        $hasOverlap = Reservation::where('space_id', $space->id)
            ->overlapping($request->start_time, $request->end_time)
            ->exists();

        if ($hasOverlap) {
            return $this->errorResponse('El espacio ya está reservado en ese horario.', 422);
        }

        // Regla de negocio 2: Máximo 2 reservas pending o confirmed simultáneas a futuro
        $futureActiveCount = Reservation::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('start_time', '>', now())
            ->count();

        if ($futureActiveCount >= 2) {
            return $this->errorResponse('No puedes tener más de 2 reservas activas (pendientes o confirmadas) a futuro.', 422);
        }

        $reservation = Reservation::create([
            'user_id' => $user->id,
            'space_id' => $space->id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'status' => 'pending',
            'notes' => $request->notes,
        ]);

        return $this->successResponse(
            $reservation->load('space.spaceType'),
            'Reserva creada correctamente en estado pendiente.',
            201
        );
    }

    /**
     * RF03: Cancelar reserva propia si aún no ha iniciado.
     */
    public function cancel(Request $request, Reservation $reservation): JsonResponse
    {
        if ($reservation->user_id !== $request->user()->id) {
            return $this->errorResponse('Solo puedes cancelar tus propias reservas.', 403);
        }

        if (!in_array($reservation->status, ['pending', 'confirmed'])) {
            return $this->errorResponse('Esta reserva no puede ser cancelada en su estado actual.', 422);
        }

        if (now()->gte($reservation->start_time)) {
            return $this->errorResponse('No es posible cancelar una reserva cuya hora de inicio ya pasó.', 422);
        }

        $reservation->update(['status' => 'cancelled']);

        return $this->successResponse($reservation, 'Reserva cancelada correctamente.');
    }

    /**
     * RF04: Ver todas las reservas pendientes (Staff / Admin).
     */
    public function pending(): JsonResponse
    {
        $pending = Reservation::with(['user:id,name,email', 'space.spaceType'])
            ->where('status', 'pending')
            ->orderBy('start_time')
            ->get();

        return $this->successResponse($pending, 'Listado de reservas pendientes');
    }

    /**
     * RF05: Confirmar o rechazar una reserva (Staff / Admin).
     */
    public function approveOrReject(Request $request, Reservation $reservation): JsonResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'in:approve,reject'],
        ], [
            'action.in' => 'La acción debe ser approve o reject.',
        ]);

        if ($reservation->status !== 'pending') {
            return $this->errorResponse('Solo se pueden aprobar o rechazar reservas en estado pendiente.', 422);
        }

        $newStatus = $validated['action'] === 'approve' ? 'confirmed' : 'cancelled';
        $reservation->update(['status' => $newStatus]);

        $msg = $newStatus === 'confirmed'
            ? 'Reserva confirmada correctamente.'
            : 'Reserva rechazada correctamente.';

        return $this->successResponse($reservation->load(['user:id,name', 'space']), $msg);
    }

    /**
     * RF06: Marcar reserva como completada al finalizar (Staff / Admin).
     */
    public function complete(Reservation $reservation): JsonResponse
    {
        if ($reservation->status !== 'confirmed') {
            return $this->errorResponse('Solo se pueden completar reservas previamente confirmadas.', 422);
        }

        $reservation->update(['status' => 'completed']);

        return $this->successResponse($reservation, 'Reserva marcada como completada.');
    }
}
