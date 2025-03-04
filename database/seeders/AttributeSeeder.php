<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attribute_data = [
            [
                'name' => 'department',
                'type' => 'text',
            ],
            [
                'name' => 'start_date',
                'type' => 'date',
            ],
            [
                'name' => 'end_date',
                'type' => 'date',
            ]
        ];

        $createAttribute = DB::table('attributes')->insert($attribute_data);
    }
}
