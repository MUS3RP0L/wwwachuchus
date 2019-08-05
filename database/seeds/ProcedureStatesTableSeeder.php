<?php

use Illuminate\Database\Seeder;
use Muserpol\Models\ProcedureState;

class ProcedureStatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $statuses = [
            ['name' => 'En proceso', 'description' => ''],
            ['name' => 'Pendiente', 'description' => ''],
            ['name' => 'Eliminado', 'description' => ''],
        ];
        foreach ($statuses as $status) {
            ProcedureState::create($status);
        }
    }
}
