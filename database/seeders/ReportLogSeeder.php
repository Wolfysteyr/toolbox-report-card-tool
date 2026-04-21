<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ReportLogSeeder extends Seeder
{
    public function run(): void
    {
        // Define some pool data to make the "random" data look realistic
        $cars = [
            ['carno' => 'AT5794', 'brand' => 'VOLVO FL-10 (AC-50)', 'tank' => 170, 'paper_cons' => 40.0, 'engine' => 9600, 'manager' => 'Normunds Eglītis'],
            ['carno' => 'JS3988', 'brand' => 'VW CADDY', 'tank' => 60, 'paper_cons' => 9.8, 'engine' => 1197, 'manager' => 'Normunds Eglītis'],
            ['carno' => 'CU2304', 'brand' => 'MAN 14.224 (AC-50)', 'tank' => 118, 'paper_cons' => 40.0, 'engine' => 6870, 'manager' => 'Normunds Eglītis'],
        ];

        $drivers = ['NORMUNDS EGLĪTIS', 'VILKS ULDIS', 'ĒRIKS SERGEJEVS', 'KRILOVS VLADIMIRS'];
        $fuels = ['DĪZEĻDEGVIELA', 'BENZĪNS E95'];

        foreach ($cars as $car) {
            $lastMileage = rand(10000, 50000);
            $lastVolume = null;
            $lastDate = null;

            // Generate 5 sequential records for each car
            for ($i = 0; $i < 5; $i++) {
                $currentDate = $lastDate 
                    ? Carbon::parse($lastDate)->addDays(rand(5, 15)) 
                    : Carbon::now()->subMonths(3);
                
                $currentVolume = rand(30, 100) + (rand(0, 99) / 100);
                $distanceDriven = rand(150, 500);
                $currentMileage = $lastMileage + $distanceDriven;

                DB::table('energy_local')->insert([
                    // Current Entry Data
                    'carno'               => $car['carno'],
                    'automarka'           => $car['brand'],
                    'bakas_tilpums'       => $car['tank'],
                    'paterins'            => $car['paper_cons'],
                    'motora_tilpums'      => $car['engine'],
                    'atbildigais'         => $car['manager'],
                    'driver'              => $drivers[array_rand($drivers)],
                    'product'             => $fuels[array_rand($fuels)],
                    'volume'              => $currentVolume,
                    'mileage'             => $currentMileage,
                    'summa'               => rand(0, 1) ? rand(50, 150) : null, // Can be null
                    
                    // Logic based on previous entry
                    'prev_date'           => $lastDate, 
                    'prev_volume'         => $lastVolume,
                    'prev_mileage'        => $lastMileage,
                    'mileage_consumption' => $lastDate ? $distanceDriven : null, // Redundant diff
                    
                    // Period logic (1st of the month)
                    'periods'             => $currentDate->copy()->startOfMonth()->format('Y-m-d'),
                    
                    // Timestamps if your table has them
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);

                // Update trackers for the next iteration of THIS car
                $lastDate = $currentDate->format('Y-m-d');
                $lastVolume = $currentVolume;
                $lastMileage = $currentMileage;
            }
        }
    }
}