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
        $all = Report::whereMonth('prev_date', $month)
            ->whereYear('prev_date', $year)
            ->orderBy('prev_date')
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
        $fuelEnd   = $fuelRows->last()?->volume ?? 0;
        $used      = round($fuelStart + $received - $fuelEnd, 2);
        $distance  = $last->mileage - $first->prev_mileage;

        return [
            'automarka'      => $first->automarka,
            'carno'          => $car,
            'motora_tilpums' => $first->motora_tilpums,
            'product'        => $first->product,
            'bakas_tilpums'  => $first->bakas_tilpums,
            'paterins'       => $first->paterins,
            'driver'         => $first->driver,
            'atbildigais'    => $first->atbildigais,
            'period_start'   => $first->periods,
            'period_end'     => $last->dated,
            'odo_start'      => $first->prev_mileage,
            'odo_end'        => $last->mileage,
            'distance'       => $distance,
            'fuel_start'     => round($fuelStart, 2),
            'received'       => round($received, 2),
            'used'           => $used,
            'fuel_end'       => round($fuelEnd, 2),
            'factual_cons'   => $distance > 0 ? round(($used / $distance) * 100, 2) : 0,
            'log'            => $rows->map(fn($r) => [
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