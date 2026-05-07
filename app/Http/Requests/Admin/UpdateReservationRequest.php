<?php

namespace App\Http\Requests\Admin;

use App\Models\Reservation;
use Illuminate\Foundation\Http\FormRequest;

class UpdateReservationRequest extends FormRequest
{
    /**
     * Admins/Programadores can update any reservation.
     * Regular users can only update their own reservations.
     */
    public function authorize(): bool
    {
        /** @var Reservation $reservation */
        $reservation = $this->route('reservation');

        // Cancelled reservations are immutable for everyone.
        if ($reservation->status === 'cancelada') {
            return false;
        }

        $isAdmin = auth()->user()->hasRole('Programador|Administrador');

        return $isAdmin || $reservation->user_id === auth()->id();
    }

    /**
     * Get the validation rules that apply to the request.
     * Regular users cannot change status — that field is stripped for non-admins.
     * Types are aligned with the DB schema: uuid string, date Y-m-d, time H:i, enum status.
     * Date does not enforce after_or_equal:today — allows editing existing past reservations.
     */
    public function rules(): array
    {
        $isAdmin = auth()->user()->hasRole('Programador|Administrador');

        $rules = [
            'room_id' => ['required', 'string', 'uuid', 'exists:rooms,id'],
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'date' => ['required', 'date_format:Y-m-d'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ];

        if ($isAdmin) {
            $rules['status'] = ['required', 'string', 'in:ativa,cancelada'];
        }

        return $rules;
    }

    /**
     * Business rule: prevent double-booking on update.
     * Excludes the reservation being edited from the conflict check.
     * Only runs after field-level validation passes to avoid unnecessary DB queries.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->any()) {
                return;
            }

            /** @var Reservation $reservation */
            $reservation = $this->route('reservation');

            $conflict = Reservation::where('room_id', $this->room_id)
                ->where('date', $this->date)
                ->where('status', 'ativa')
                ->where('id', '!=', $reservation->id)
                ->where('start_time', '<', $this->end_time)
                ->where('end_time', '>', $this->start_time)
                ->exists();

            if ($conflict) {
                $validator->errors()->add(
                    'conflict',
                    'Esta sala já está reservada neste horário. Escolha outro horário ou sala.'
                );
            }
        });
    }

    /**
     * Custom validation messages in pt-BR.
     */
    public function messages(): array
    {
        return [
            'room_id.required' => 'Selecione uma sala.',
            'room_id.uuid' => 'Identificador de sala inválido.',
            'room_id.exists' => 'A sala selecionada não existe ou foi removida.',
            'title.required' => 'O título da reserva é obrigatório.',
            'title.min' => 'O título deve ter no mínimo 3 caracteres.',
            'title.max' => 'O título não pode ultrapassar 255 caracteres.',
            'date.required' => 'A data da reserva é obrigatória.',
            'date.date_format' => 'A data deve estar no formato AAAA-MM-DD.',
            'start_time.required' => 'O horário de início é obrigatório.',
            'start_time.date_format' => 'O horário de início deve estar no formato HH:MM.',
            'end_time.required' => 'O horário de término é obrigatório.',
            'end_time.date_format' => 'O horário de término deve estar no formato HH:MM.',
            'end_time.after' => 'O horário de término deve ser posterior ao horário de início.',
            'status.required' => 'O status da reserva é obrigatório.',
            'status.in' => 'O status deve ser "ativa" ou "cancelada".',
        ];
    }
}
