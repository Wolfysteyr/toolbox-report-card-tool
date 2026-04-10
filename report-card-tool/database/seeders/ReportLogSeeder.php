<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportLogSeeder extends Seeder
{
    public function run(): void
    {
        $cars = [
            ['carno' => 'VR1694', 'automarka' => 'VOLVO FM',        'motora_tilpums' => 10837, 'bakas_tilpums' => 400, 'paterins' => 48.0, 'product' => 'DIZELDEGVIELA'],
            ['carno' => 'VR2201', 'automarka' => 'SCANIA R500',     'motora_tilpums' => 12740, 'bakas_tilpums' => 500, 'paterins' => 52.0, 'product' => 'DIZELDEGVIELA'],
            ['carno' => 'VR3345', 'automarka' => 'MAN TGX',         'motora_tilpums' => 10518, 'bakas_tilpums' => 350, 'paterins' => 45.0, 'product' => 'DIZELDEGVIELA'],
            ['carno' => 'VR4412', 'automarka' => 'DAF XF',          'motora_tilpums' => 12902, 'bakas_tilpums' => 450, 'paterins' => 50.0, 'product' => 'DIZELDEGVIELA'],
            ['carno' => 'VR5589', 'automarka' => 'MERCEDES ACTROS', 'motora_tilpums' => 11967, 'bakas_tilpums' => 420, 'paterins' => 46.5, 'product' => 'DIZELDEGVIELA'],
        ];

        $drivers = [
            ['driver' => 'NORMUNDS EGLITIS', 'atbildigais' => 'Normunds Eglitis'],
            ['driver' => 'JURIS KALNINS',    'atbildigais' => 'Juris Kalnins'],
            ['driver' => 'ANDRIS BERZINS',   'atbildigais' => 'Andris Berzins'],
            ['driver' => 'MARIS OZOLS',      'atbildigais' => 'Maris Ozols'],
            ['driver' => 'INTS LIEPINS',     'atbildigais' => 'Ints Liepins'],
        ];

        $months = [
            ['year' => 2025, 'month' => 10],
            ['year' => 2025, 'month' => 11],
            ['year' => 2025, 'month' => 12],
            ['year' => 2026, 'month' => 1],
            ['year' => 2026, 'month' => 2],
            ['year' => 2026, 'month' => 3],
        ];

        $records = [];

        foreach ($cars as $carIndex => $car) {
            $driver    = $drivers[$carIndex % count($drivers)];
            $odometer  = rand(8000, 15000);
            $tankLevel = round(rand(100, 250) * 1.0, 2); // starting fuel level (full-ish)

            foreach ($months as $period) {
                $year        = $period['year'];
                $mon         = $period['month'];
                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $mon, $year);
                $periodStart = Carbon::createFromDate($year, $mon, 1)->format('d.m.Y');
                $periodEnd   = Carbon::createFromDate($year, $mon, $daysInMonth)->format('d.m.Y');
                $periods     = "$periodStart - $periodEnd";

                $refuels = rand(3, 4);
                $dayStep = (int) floor($daysInMonth / ($refuels + 1));

                for ($i = 1; $i <= $refuels; $i++) {
                    $day         = min($i * $dayStep, $daysInMonth);
                    $prevDate    = Carbon::createFromDate($year, $mon, $day)->toDateString();

                    // Drive to refuel stop
                    $kmDriven     = rand(80, 400);
                    $prevMileage  = $odometer;
                    $odometer    += $kmDriven;

                    // Burn fuel while driving
                    $fuelConsumed = round(($kmDriven / 100) * $car['paterins'] * (rand(90, 115) / 100), 2);
                    $tankLevel    = max(10, round($tankLevel - $fuelConsumed, 2)); // deplete, min 10L so tank never runs dry

                    // prev_volume = tank level AFTER driving, BEFORE refueling
                    $prevVolume   = $tankLevel;

                    // Refuel
                    $maxRefuel    = round($car['bakas_tilpums'] - $tankLevel, 2);
                    $refuelAmount = round(rand(80, max(80, (int)$maxRefuel)), 2);
                    $tankLevel    = min(round($tankLevel + $refuelAmount, 2), $car['bakas_tilpums']);

                    // volume = tank level AFTER refueling
                    $volume = $tankLevel;
                    $summa  = round($refuelAmount * (rand(155, 175) / 100), 2);
                    $mileageConsumption = round(($fuelConsumed / $kmDriven) * 100, 2);

                    $records[] = [
                        'prev_date'           => $prevDate,
                        'periods'             => $periods,
                        'volume'              => round($volume, 2),
                        'prev_volume'         => round($prevVolume, 2),
                        'summa'               => $summa,
                        'mileage'             => (float) $odometer,
                        'prev_mileage'        => (float) $prevMileage,
                        'mileage_consumption' => $mileageConsumption,
                        'product'             => $car['product'],
                        'carno'               => $car['carno'],
                        'driver'              => $driver['driver'],
                        'bakas_tilpums'       => $car['bakas_tilpums'],
                        'paterins'            => $car['paterins'],
                        'motora_tilpums'      => $car['motora_tilpums'],
                        'automarka'           => $car['automarka'],
                        'atbildigais'         => $driver['atbildigais'],
                    ];
                }
            }
        }

        foreach (array_chunk($records, 50) as $chunk) {
            DB::table('energy_local')->insert($chunk);
        }

        $this->command->info('Seeded ' . count($records) . ' energy_local records.');
    }
}