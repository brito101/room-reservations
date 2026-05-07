<?php

namespace App\Http\Requests\Admin;

use App\Models\Reservation;
use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Authorization (auth middleware) is handled at the route level.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * Types are aligned with the DB schema: uuid string, date Y-m-d, time H:i.
     */
    public function rules(): array
    {
        return [
            'room_id' => ['required', 'string', 'uuid', 'exists:rooms,id'],
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ];
    }

    /**
     * Business rule: prevent double-booking the same room on overlapping time slots.
     * Only runs after field-level validation passes to avoid unnecessary DB queries.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->any()) {
                return;
            }

            $conflict = Reservation::where('room_id', $this->room_id)
                ->where('date', $this->date)
                ->where('status', 'ativa')
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
            'date.after_or_equal' => 'Não é possível criar reservas para datas passadas.',
            'start_time.required' => 'O horário de início é obrigatório.',
            'start_time.date_format' => 'O horário de início deve estar no formato HH:MM.',
            'end_time.required' => 'O horário de término é obrigatório.',
            'end_time.date_format' => 'O horário de término deve estar no formato HH:MM.',
            'end_time.after' => 'O horário de término deve ser posterior ao horário de início.',
        ];
    }
}
