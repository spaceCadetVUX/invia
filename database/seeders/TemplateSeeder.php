<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Template::firstOrCreate(
            ['blade_file' => 'classic'],
            [
                'name'      => 'Classic Gold',
                'category'  => 'wedding',
                'price'     => 0,
                'version'   => 1,
                'is_active' => true,
            ]
        );
    }
}
