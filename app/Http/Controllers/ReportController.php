<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ReportController extends Controller
{
    public function copy()
    {
        $api_key = Setting::where('key', 'porter_api_key')->value('value');

        if (! $api_key) {
            return response()->json(['message' => 'Nav API atslēgas!'], 400);
        }

        // Update the URL to the correct endpoint provided by Porter
        $url = 'http://localhost:80/api/templates/celazimes/json';
        $response = Http::withHeaders(['X-API-Key' => $api_key])->get($url);

        if ($response->failed()) {
            return response()->json(['message' => 'Porter sync failed.'], 500);
        }

        $data = json_decode($response->body(), true);

        $syncedCars = [];

        foreach ($data as $item) {
            Report::updateOrCreate(
                ['carno' => $item['carno'], 'dated' => $item['dated'], 'volume' => $item['volume']],
                [
                    'periods' => $item['periods'] ?? '',
                    'prev_volume' => $item['prev_volume'] ?? 0,
                    'summa' => $item['summa'] ?? 0,
                    'mileage' => $item['mileage'] ?? 0,
                    'prev_mileage' => $item['prev_mileage'] ?? 0,
                    'mileage_consumption' => $item['mileage_consumption'] ?? 0,
                    'product' => $item['product'] ?? '',
                    'driver' => $item['driver'] ?? '',
                    'bakas_tilpums' => $item['bakas_tilpums'] ?? 0,
                    'paterins' => $item['paterins'] ?? 0,
                    'motora_tilpums' => $item['motora_tilpums'] ?? '',
                    'automarka' => $item['automarka'] ?? '',
                    'atbildigais' => $item['atbildigais'] ?? '',
                ]
            );
            $syncedCars[] = $item['carno'];
        }

        foreach (array_unique($syncedCars) as $car) {
            $months = Report::where('carno', $car)
                ->selectRaw('YEAR(dated) as year, MONTH(dated) as month')
                ->distinct()
                ->orderByRaw('year ASC, month ASC')
                ->get();

            foreach ($months as $monthData) {
                $rows = Report::where('carno', $car)
                    ->whereMonth('dated', $monthData->month)
                    ->whereYear('dated', $monthData->year)
                    ->orderBy('dated')
                    ->get();

                $calc = $this->calculateEndValues($rows);

                $nextMonth = Carbon::create($monthData->year, $monthData->month, 1)->addMonth();

                $nextFirstEntry = Report::where('carno', $car)
                    ->whereMonth('dated', $nextMonth->month)
                    ->whereYear('dated', $nextMonth->year)
                    ->orderBy('dated')
                    ->first();

                if ($nextFirstEntry) {
                    Report::where('carno', $car)
                        ->whereMonth('dated', $nextMonth->month)
                        ->whereYear('dated', $nextMonth->year)
                        ->update([
                            'prev_volume' => $calc['fakeFuelEnd'],
                            'prev_mileage' => $calc['odoEnd'],
                        ]);
                }
            }
        }

        return response()->json(['message' => 'Dati veiksmīgi sinhronizēti.']);
    }

    /**
     * The Missing Bridge Method for Report.jsx
     */
    public function fetchDataFromLocal($car, $month, $year)
    {
        $rows = Report::where('carno', $car)
            ->whereMonth('dated', $month)
            ->whereYear('dated', $year)
            ->orderBy('dated')
            ->get();

        if ($rows->isEmpty()) {
            return response()->json(null);
        }

        // We use a private helper to ensure the format matches fetchAllForPeriod
        return response()->json($this->formatReport($rows, $car));
    }

    public function fetchAllForPeriod($month, $year)
    {
        $all = Report::whereMonth('dated', $month)
            ->whereYear('dated', $year)
            ->orderBy('dated')
            ->get()
            ->groupBy('carno');

        $reports = $all->map(fn ($rows, $car) => $this->formatReport($rows, $car))->values();

        return response()->json($reports);
    }

    private function calculateEndValues($rows): array
    {
        $first = $rows->first();
        $last = $rows->last();

        $fuelRows = $rows->filter(fn ($r) => ! str_contains(strtolower($r->product), strtolower('VĒJSTIKLU ŠĶIDRUMS')));
        $received = $fuelRows->sum('volume');
        $fuelStart = $fuelRows->first()?->prev_volume ?? 0;
        $distance = $last->mileage - $first->prev_mileage;
        $fuelEnd = $fuelRows->last()?->volume ?? 0;
        $used = round($fuelStart + $received - $fuelEnd, 2);
        $factualCons = $distance > 0 ? round(($used / $distance) * 100, 2) : 0;

        $odoPeriodDays = Carbon::parse($first->periods)->daysInMonth;
        $lastFillupDay = Carbon::parse($last->dated)->day;

        if ($lastFillupDay === $odoPeriodDays) {
            $estimatedOdoEnd = 0;
            $fakeDistance = $distance;
            $fakeUsed = $used;
            $fakeFuelEnd = $fuelEnd;
        } else {
            $avgKmPerDay = $lastFillupDay > 0 ? ($distance / $lastFillupDay) : 2;
            $estimatedOdoEnd = round($avgKmPerDay * ($odoPeriodDays - $lastFillupDay), 0);
            $fakeDistance = $distance + $estimatedOdoEnd;
            $fakeUsed = round($factualCons * $fakeDistance / 100, 2);
            $fakeFuelEnd = round($fuelEnd - ($fakeUsed - $used), 2);

            
            if ($fakeFuelEnd < 5 && $factualCons > 0) {
                $maxFakeUsed = $fuelEnd + $used - 5;
                $fakeDistance = round($maxFakeUsed * 100 / $factualCons, 0);
                $estimatedOdoEnd = $fakeDistance - $distance;
                $fakeUsed = round($factualCons * $fakeDistance / 100, 2);
                $fakeFuelEnd = 5;
            }

        }

        if ($factualCons <= 0) {
            $factualCons = $first->paterins ?? 0;
        }

        return [
            'fuelStart' => round($fuelStart, 2),
            'received' => round($received, 2),
            'distance' => $distance,
            'fuelEnd' => round($fuelEnd, 2),
            'used' => $used,
            'factualCons' => $factualCons,
            'estimatedOdoEnd' => $estimatedOdoEnd,
            'fakeDistance' => $fakeDistance,
            'fakeUsed' => $fakeUsed,
            'fakeFuelEnd' => $fakeFuelEnd,
            'odoEnd' => $last->mileage + $estimatedOdoEnd,
        ];
    }

    private function formatReport($rows, $car)
    {
        $first = $rows->first();
        $last = $rows->last();

        $calc = $this->calculateEndValues($rows);

        return [
            'automarka' => $first->automarka,
            'carno' => $car,
            'motora_tilpums' => $first->motora_tilpums,
            'product' => $first->product,
            'bakas_tilpums' => $first->bakas_tilpums,
            'paterins' => $first->paterins,
            'driver' => $first->driver,
            'atbildigais' => $first->atbildigais,
            'period_start' => $first->periods,
            'period_last_fillup' => $last->dated,
            'period_end' => Carbon::parse($first->periods)->endOfMonth()->toDateString(),
            'odo_start' => $first->prev_mileage,
            'odo_last_fillup' => $last->mileage,
            'odo_end' => $calc['odoEnd'],
            'distance' => $calc['distance'],
            'fake_distance' => $calc['fakeDistance'],
            'fuel_start' => $calc['fuelStart'],
            'received' => $calc['received'],
            'used' => $calc['used'],
            'fake_used' => $calc['fakeUsed'],
            'fuel_end' => $calc['fuelEnd'],
            'fake_fuel_end' => $calc['fakeFuelEnd'],
            'factual_cons' => $calc['factualCons'],
            'log' => $rows->map(fn ($r) => [
                'date' => $r->dated,
                'product' => $r->product,
                'amount' => $r->volume,
                'summa' => $r->summa,
                'driver' => $r->driver,
            ])->values(),
        ];
    }

    public function getAvailableData()
    {
        return Report::selectRaw('YEAR(dated) as year, MONTH(dated) as month, carno')
            ->distinct()
            ->orderByRaw('year DESC, month DESC, carno ASC')
            ->get();
    }

    public function updateApiKey(Request $request)
    {
        Setting::updateOrCreate(['key' => 'porter_api_key'], ['value' => $request->api_key]);

        return response()->json(['message' => 'API atslēga saglabāta.']);
    }

    public function getApiKey()
    {
        $val = Setting::where('key', 'porter_api_key')->value('value');

        return response()->json(['api_key' => $val]);
    }
}
