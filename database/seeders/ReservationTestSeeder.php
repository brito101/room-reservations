<?php

namespace Database\Seeders;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReservationTestSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command->warn('ReservationTestSeeder ignorado: ambiente não é local.');

            return;
        }

        $rooms = Room::pluck('id')->toArray();
        $users = User::pluck('id')->toArray();

        if (empty($rooms) || empty($users)) {
            $this->command->warn('ReservationTestSeeder: nenhuma sala ou usuário encontrado.');

            return;
        }

        $reservations = [
            [
                'room_id' => $rooms[0],
                'user_id' => $users[0],
                'title' => 'Reunião de Planejamento Q2',
                'date' => now()->addDays(1)->format('Y-m-d'),
                'start_time' => '08:00',
                'end_time' => '09:00',
                'status' => 'ativa',
            ],
            [
                'room_id' => $rooms[1 % count($rooms)],
                'user_id' => $users[0],
                'title' => 'Treinamento de Integração',
                'date' => now()->addDays(1)->format('Y-m-d'),
                'start_time' => '10:00',
                'end_time' => '12:00',
                'status' => 'ativa',
            ],
            [
                'room_id' => $rooms[2 % count($rooms)],
                'user_id' => $users[count($users) > 1 ? 1 : 0],
                'title' => 'Reunião de Diretoria',
                'date' => now()->addDays(2)->format('Y-m-d'),
                'start_time' => '09:00',
                'end_time' => '10:30',
                'status' => 'ativa',
            ],
            [
                'room_id' => $rooms[0],
                'user_id' => $users[count($users) > 1 ? 1 : 0],
                'title' => 'Sprint Review',
                'date' => now()->addDays(3)->format('Y-m-d'),
                'start_time' => '14:00',
                'end_time' => '15:30',
                'status' => 'ativa',
            ],
            [
                'room_id' => $rooms[3 % count($rooms)],
                'user_id' => $users[0],
                'title' => 'Aula de Laravel',
                'date' => now()->addDays(4)->format('Y-m-d'),
                'start_time' => '13:00',
                'end_time' => '17:00',
                'status' => 'ativa',
            ],
            [
                'room_id' => $rooms[1 % count($rooms)],
                'user_id' => $users[count($users) > 1 ? 1 : 0],
                'title' => 'Onboarding Novos Colaboradores',
                'date' => now()->addDays(5)->format('Y-m-d'),
                'start_time' => '09:00',
                'end_time' => '11:00',
                'status' => 'ativa',
            ],
            [
                'room_id' => $rooms[2 % count($rooms)],
                'user_id' => $users[0],
                'title' => 'Alinhamento Comercial',
                'date' => now()->addDays(6)->format('Y-m-d'),
                'start_time' => '15:00',
                'end_time' => '16:00',
                'status' => 'ativa',
            ],
            [
                'room_id' => $rooms[0],
                'user_id' => $users[0],
                'title' => 'Apresentação de Resultados',
                'date' => now()->addDays(7)->format('Y-m-d'),
                'start_time' => '10:00',
                'end_time' => '11:30',
                'status' => 'ativa',
            ],
            [
                'room_id' => $rooms[3 % count($rooms)],
                'user_id' => $users[count($users) > 1 ? 1 : 0],
                'title' => 'Hackathon Interno',
                'date' => now()->addDays(8)->format('Y-m-d'),
                'start_time' => '08:00',
                'end_time' => '18:00',
                'status' => 'ativa',
            ],
            [
                'room_id' => $rooms[1 % count($rooms)],
                'user_id' => $users[0],
                'title' => 'Workshop de DevSecOps',
                'date' => now()->addDays(9)->format('Y-m-d'),
                'start_time' => '09:00',
                'end_time' => '12:00',
                'status' => 'cancelada',
            ],
        ];

        foreach ($reservations as $data) {
            Reservation::create($data);
        }

        $this->command->info('10 reservas de teste criadas com sucesso.');
    }
}
