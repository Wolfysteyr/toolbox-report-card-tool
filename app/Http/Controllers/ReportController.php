<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class ReportController extends Controller
{
    public function copy()
    {
        $api_key = Setting::where('key', 'porter_api_key')->value('value');
        
        if (!$api_key) return response()->json(['message' => 'Nav API atslēgas!'], 400);

        // Update the URL to the correct endpoint provided by Porter
        $url = "http://localhost:80/api/templates/celazimes/json";
        $response = Http::withHeaders(['X-API-Key' => $api_key])->get($url);

        if ($response->failed()) return response()->json(['message' => 'Porter sync failed.'], 500);

        $data = json_decode($response->body(), true);

        foreach ($data as $item) {
            Report::updateOrCreate(
                ['carno' => $item['carno'], 'dated' => $item['dated'], 'volume' => $item['volume']],
                [
                    'periods'             => $item['periods'] ?? '',
                    'prev_volume'         => $item['prev_volume'] ?? 0,
                    'summa'               => $item['summa'] ?? 0,
                    'mileage'             => $item['mileage'] ?? 0,
                    'prev_mileage'        => $item['prev_mileage'] ?? 0,
                    'mileage_consumption' => $item['mileage_consumption'] ?? 0,
                    'product'             => $item['product'] ?? '',
                    'driver'              => $item['driver'] ?? '',
                    'bakas_tilpums'       => $item['bakas_tilpums'] ?? 0,
                    'paterins'            => $item['paterins'] ?? 0,
                    'motora_tilpums'      => $item['motora_tilpums'] ?? '',
                    'automarka'           => $item['automarka'] ?? '',
                    'atbildigais'         => $item['atbildigais'] ?? '',
                ]
            );
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

        if ($rows->isEmpty()) return response()->json(null);

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

        $reports = $all->map(fn($rows, $car) => $this->formatReport($rows, $car))->values();

        return response()->json($reports);
    }

    private function formatReport($rows, $car)
    {
        $first = $rows->first();
        $last = $rows->last();


         // Only fuel rows for calculations
        $fuelRows = $rows->filter(fn($r) => !str_contains(strtolower($r->product), 'VĒJSTIKLU ŠĶIDRUMS'));
        
        $received  = $fuelRows->sum('volume');
        $fuelStart = $fuelRows->first()?->prev_volume ?? 0;

        $distance     = $last->mileage - $first->prev_mileage;


        // get the amount of days in the period
        $odoPeriodDays = \Carbon\Carbon::parse($first->periods)->daysInMonth;
        // get the average km/day based by dividing distance by the day number of the last fillup in the period
        $lastFillupDay = \Carbon\Carbon::parse($last->dated)->day;
        
        $avgKmPerDay   = $lastFillupDay > 0 ? ($distance / $lastFillupDay) : 2;
        // then we calculate the amount of km that we need to add to the existing odo end to get the estimated odo end for the period
        // round to 0 decimals since odo readings are whole numbers
        // also subtract the last fillup day from the odo period days to get the remaining days in the period after the last fillup
        $estimatedOdoEnd = round($avgKmPerDay * ($odoPeriodDays-$lastFillupDay), 0);

        $fakeDistance = $distance + $estimatedOdoEnd; // this is the total of both actual and fake distance

        // we need to calculate the fuel consumed and fuel_end using the new estimated odo end
        $fuelEnd     = $fuelRows->last()?->volume ?? 0;
        $used        = round($fuelStart + $received - $fuelEnd, 2); // fuel used based on actual data we have

        $factualCons = $distance > 0 ? round(($used / $distance) * 100, 2) : 0;
        $fakeUsed    = round($factualCons * $fakeDistance / 100, 2); // fake fuel used calculated based on estimated odo end
        $fakeFuelEnd = round($fuelEnd - ($fakeUsed - $used), 2); // fake fuel end calculated based on fake fuel used (removing the extra fuel used that we added in fake used to get the fuel end)



        return [
            'automarka'          => $first->automarka,
            'carno'              => $car,
            'motora_tilpums'     => $first->motora_tilpums,
            'product'            => $first->product,
            'bakas_tilpums'      => $first->bakas_tilpums,
            'paterins'           => $first->paterins,
            'driver'             => $first->driver,
            'atbildigais'        => $first->atbildigais,
            'period_start'       => $first->periods,
            'period_last_fillup' => $last->dated,
            'period_end'         => \Carbon\Carbon::parse($first->periods)->endOfMonth()->toDateString(),
            'odo_start'          => $first->prev_mileage,
            'odo_last_fillup'    => $last->mileage,
            'odo_end'            => $last->mileage + $estimatedOdoEnd,
            'distance'           => $distance,
            'fake_distance'     => $fakeDistance, // this is the distance calculation based on estimated odo end
            'fuel_start'         => round($fuelStart, 2),
            'received'           => round($received, 2),
            'used'               => $used,
            'fake_used'           => $fakeUsed, // this is the fuel used calculation based on estimated odo end
            'fuel_end'           => round($fuelEnd, 2),
            'fake_fuel_end'       => $fakeFuelEnd, // this is the fuel end calculation based on estimated odo end
            'factual_cons'       => $factualCons,
            'log'                => $rows->map(fn($r) => [
                'date'    => $r->dated,
                'product' => $r->product,
                'amount'  => $r->volume,
                'summa'   => $r->summa,
                'driver'  => $r->driver,
            ])->values(),
        ];
    }

    public function getAvailableData()
    {
        return Report::selectRaw("YEAR(dated) as year, MONTH(dated) as month, carno")
            ->distinct()
            ->orderByRaw("year DESC, month DESC, carno ASC")
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