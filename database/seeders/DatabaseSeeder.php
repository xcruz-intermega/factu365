<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Plan::create([
            'name' => 'Free',
            'slug' => 'free',
            'max_invoices' => 50,
            'max_users' => 1,
            'price' => 0,
        ]);

        Plan::create([
            'name' => 'Autónomo',
            'slug' => 'autonomo',
            'max_invoices' => 500,
            'max_users' => 2,
            'price' => 9.99,
        ]);

        Plan::create([
            'name' => 'PYME',
            'slug' => 'pyme',
            'max_invoices' => 0,
            'max_users' => 10,
            'price' => 29.99,
        ]);
    }
}
