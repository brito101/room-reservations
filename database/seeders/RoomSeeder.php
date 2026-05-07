<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $rooms = [
            ['name' => 'Sala A - Reunião', 'capacity' => 8, 'description' => 'Sala de reuniões com TV e quadro branco.'],
            ['name' => 'Sala B - Treinamento', 'capacity' => 20, 'description' => 'Sala de treinamento com projetor.'],
            ['name' => 'Sala C - Diretoria', 'capacity' => 6, 'description' => 'Sala reservada para reuniões da diretoria.'],
            ['name' => 'Laboratório', 'capacity' => 15, 'description' => 'Laboratório de informática.'],
        ];

        foreach ($rooms as $room) {
            Room::firstOrCreate(['name' => $room['name']], $room);
        }
    }
}
