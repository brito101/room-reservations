<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreReservationRequest;
use App\Http\Requests\Admin\UpdateReservationRequest;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    /**
     * Display a listing of reservations and the create form.
     */
    public function index()
    {
        $reservations = Reservation::with(['room', 'user'])
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc')
            ->get();

        $rooms = Room::orderBy('name')->get();

        return view('admin.reservations.index', compact('reservations', 'rooms'));
    }

    /**
     * Store a new reservation.
     * Validation and overlap check are handled by StoreReservationRequest.
     */
    public function store(StoreReservationRequest $request): RedirectResponse
    {
        Reservation::create(array_merge(
            $request->validated(),
            ['user_id' => Auth::id(), 'status' => 'ativa']
        ));

        return redirect()->route('admin.reservations.index')
            ->with('success', 'Reserva realizada com sucesso!');
    }

    /**
     * Show the edit form for a reservation.
     * Cancelled reservations cannot be edited by anyone.
     * Admins can edit any active reservation; users can only edit their own.
     */
    public function edit(Reservation $reservation)
    {
        if ($reservation->status === 'cancelada') {
            return redirect()->route('admin.reservations.index')
                ->with('error', 'Reservas canceladas não podem ser editadas.');
        }

        $isAdmin = Auth::user()->hasRole('Programador|Administrador');

        if (! $isAdmin && $reservation->user_id !== Auth::id()) {
            abort(403, 'Você só pode editar suas próprias reservas.');
        }

        $rooms = Room::orderBy('name')->get();

        return view('admin.reservations.edit', compact('reservation', 'rooms', 'isAdmin'));
    }

    /**
     * Update an existing reservation.
     * Authorization, validation and overlap check are handled by UpdateReservationRequest.
     */
    public function update(UpdateReservationRequest $request, Reservation $reservation): RedirectResponse
    {
        $reservation->update($request->validated());

        return redirect()->route('admin.reservations.index')
            ->with('success', 'Reserva atualizada com sucesso!');
    }

    /**
     * Cancel a reservation.
     * Admins/Programadores can cancel any reservation.
     * Regular users can only cancel their own active reservations.
     */
    public function cancel(Reservation $reservation): RedirectResponse
    {
        $isAdmin = Auth::user()->hasRole('Programador|Administrador');

        if (! $isAdmin && $reservation->user_id !== Auth::id()) {
            abort(403, 'Você só pode cancelar suas próprias reservas.');
        }

        if (! $isAdmin && $reservation->status !== 'ativa') {
            abort(403, 'Apenas reservas ativas podem ser canceladas.');
        }

        $reservation->update(['status' => 'cancelada']);

        return redirect()->route('admin.reservations.index')
            ->with('success', 'Reserva cancelada com sucesso!');
    }

    /**
     * Permanently delete a reservation.
     * Admins can delete any reservation; users can only delete their own.
     */
    public function destroy(Reservation $reservation): RedirectResponse
    {
        $isAdmin = Auth::user()->hasRole('Programador|Administrador');

        if (! $isAdmin && $reservation->user_id !== Auth::id()) {
            abort(403, 'Você só pode excluir suas próprias reservas.');
        }

        $reservation->forceDelete();

        return redirect()->route('admin.reservations.index')
            ->with('success', 'Reserva excluída com sucesso!');
    }
}
