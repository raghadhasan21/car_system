<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CarSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('cars')->insert([
            [
                'brand' => 'Toyota',
                'model' => 'Camry',
                'year' => 2022,
                'price' => 85000,
                'mileage' => 15000,
                'fuel_type' => 'بنزين',
                'transmission' => 'automatic',
                'color' => 'أسود',
                'description' => 'سيارة ممتازة بحالة الوكالة - فتحة سقف',
                'contact_phone' => '0999999999',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'brand' => 'BMW',
                'model' => 'X5',
                'year' => 2023,
                'price' => 250000,
                'mileage' => 5000,
                'fuel_type' => 'ديزل',
                'transmission' => 'automatic',
                'color' => 'أبيض',
                'description' => 'BMW X5 M Sport كامل المواصفات - تحت الضمان',
                'contact_phone' => '0988888888',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'brand' => 'Kia',
                'model' => 'Sportage',
                'year' => 2021,
                'price' => 65000,
                'mileage' => 40000,
                'fuel_type' => 'بنزين',
                'transmission' => 'automatic',
                'color' => 'رمادي',
                'description' => 'دفع رباعي - فتحة سقف - شاشة لمس',
                'contact_phone' => '0977777777',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'brand' => 'Tesla',
                'model' => 'Model 3',
                'year' => 2024,
                'price' => 180000,
                'mileage' => 0,
                'fuel_type' => 'كهرباء',
                'transmission' => 'automatic',
                'color' => 'أزرق',
                'description' => 'جديدة - وكالة - قيادة ذاتية',
                'contact_phone' => '0966666666',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'brand' => 'Hyundai',
                'model' => 'Tucson',
                'year' => 2022,
                'price' => 75000,
                'mileage' => 25000,
                'fuel_type' => 'بنزين',
                'transmission' => 'automatic',
                'color' => 'فضي',
                'description' => 'حالة ممتازة - بصمة - كاميرا خلفية',
                'contact_phone' => '0955555555',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}