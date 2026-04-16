<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;
use Illuminate\Support\Facades\Http;
use App\Models\Setting;

class ReportController extends Controller
{
    public function copy()
    {
        $data = $this->fetchDataFromPorter();
        if (!$data) return response()->json(['message' => 'Failed to fetch data.'], 500);

        $data = json_decode($data, true);

        foreach ($data as $item) {
            Report::updateOrCreate(
                ['carno' => $item['carno'], 'prev_date' => $item['prev_date'], 'volume' => $item['volume']],
                [
                    'periods'             => $item['periods'],
                    'prev_volume'         => $item['prev_volume'],
                    'summa'               => $item['summa'],
                    'mileage'             => $item['mileage'],
                    'prev_mileage'        => $item['prev_mileage'],
                    'mileage_consumption' => $item['mileage_consumption'],
                    'product'             => $item['product'],
                    'driver'              => $item['driver'],
                    'bakas_tilpums'       => $item['bakas_tilpums'],
                    'paterins'            => $item['paterins'],
                    'motora_tilpums'      => $item['motora_tilpums'],
                    'automarka'           => $item['automarka'],
                    'atbildigais'         => $item['atbildigais'],
                ]
            );
        }
        return response()->json(['message' => 'Data synced successfully.']);
    }

    public function fetchDataFromPorter()
    {
        $setting = Setting::where('key', 'porter_api_url')->first();
        $api_key = $setting ? $setting->value : null;
        $url = "http://localhost:80/api/templates/celazimes/json";
        $response = Http::withHeaders(['X-API-Key' => $api_key])->get($url);
        return $response->failed() ? null : $response->body();
    }

    public function updateApiKey(Request $request)
    {
        $request->validate(['api_key' => 'required|string']);

        Setting::updateOrCreate(
            ['key' => 'porter_api_key'],
            ['value' => $request->api_key]
        );

        return response()->json(['message' => 'API atslēga saglabāta.']);
    }

    public function getApiKey()
    {
        $setting = Setting::where('key', 'porter_api_key')->first();
        return response()->json(['api_key' => $setting ? $setting->value : null]);
    }

    /**
     * Optimized Bulk Fetcher for Production
     */
    public function fetchAllForPeriod($month, $year)
    {
        $allRows = Report::whereMonth('prev_date', $month)
            ->whereYear('prev_date', $year)
            ->orderBy('prev_date')
            ->get()
            ->groupBy('carno');

        $reports = $allRows->map(function ($rows, $carno) {
            $first = $rows->first();
            $last  = $rows->last();

            $odoStart = $first->prev_mileage;
            $odoEnd   = $last->mileage;
            $distance = $odoEnd - $odoStart;
            $fuelStart = $first->prev_volume;
            $fuelEnd   = $last->volume;
            $received  = $rows->sum('volume');
            $used      = round($fuelStart + $received - $fuelEnd, 2);
            $factualCons = $distance > 0 ? round(($used / $distance) * 100, 2) : 0;

            return [
                'automarka'      => $first->automarka,
                'carno'          => $carno,
                'motora_tilpums' => $first->motora_tilpums,
                'product'        => $first->product,
                'bakas_tilpums'  => $first->bakas_tilpums,
                'paterins'       => $first->paterins,
                'driver'         => $first->driver,
                'atbildigais'    => $first->atbildigais,
                'period_start'   => $first->prev_date,
                'period_end'     => $last->prev_date,
                'odo_start'      => $odoStart,
                'odo_end'        => $odoEnd,
                'distance'       => $distance,
                'fuel_start'     => $fuelStart,
                'received'       => $received,
                'used'           => $used,
                'fuel_end'       => round($fuelEnd, 2),
                'factual_cons'   => $factualCons,
                'log'            => $rows->map(fn($r) => [
                    'date'    => $r->prev_date,
                    'product' => $r->product,
                    'amount'  => $r->volume,
                    'summa'   => $r->summa,
                    'driver'  => $r->driver,
                ])->values(),
            ];
        })->values();

        return response()->json($reports);
    }

    public function getAvailableData()
    {
        // MySQL Specific Syntax
        $rows = Report::selectRaw("YEAR(prev_date) as year, MONTH(prev_date) as month, carno")
            ->distinct()
            ->orderByRaw("year, month, carno")
            ->get()
            ->map(fn($r) => [
                'year'  => (int) $r->year,
                'month' => (int) $r->month,
                'carno' => $r->carno,
            ]);

        return response()->json($rows);
    }
}