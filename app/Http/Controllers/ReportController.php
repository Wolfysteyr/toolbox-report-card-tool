<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;
use Illuminate\Support\Facades\Http;

class ReportController extends Controller
{
    public function copy()
    {
        $data = $this->fetchDataFromPorter();

        if (!$data) {
            return response()->json(['message' => 'Failed to fetch data from Porter.'], 500);
        }

        $data = json_decode($data, true);

        foreach ($data as $item) {
            Report::updateOrCreate(
                [
                    'carno'     => $item['carno'],
                    'prev_date' => $item['prev_date'],
                    'volume'    => $item['volume'],
                ],
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
        $api_key = "2e635e5e1a124ba425c87741e212e155"; // temporary hardcoded API key, should be moved to .env
        $url = "http://localhost:80/api/templates/Degvielas/json";

        $response = Http::withHeaders([
            'X-API-Key' => $api_key,
        ])->get($url);

        if ($response->failed()){
            return null;
        }

        return $response->body();
    }

    public function fetchDataFromLocal($car, $month, $year)
    {
        $rows = Report::where('carno', $car)
            ->whereMonth('prev_date', $month)
            ->whereYear('prev_date', $year)
            ->orderBy('prev_date')
            ->get();

        if ($rows->isEmpty()) {
            return response()->json(null);
        }

        $first = $rows->first();
        $last  = $rows->last();

        $odoStart  = $first->prev_mileage;
        $odoEnd    = $last->mileage;
        $distance  = $odoEnd - $odoStart;

        $fuelStart = $first->prev_volume; // gets the fuel volume at the start of the period, grabbing the previous month's last entry
        $fuelEnd   = $last->volume;
        $received  = $rows->sum(fn($r) => $r->volume);
        $used      = round($fuelStart + $received - $fuelEnd, 2);

        $factualCons = $distance > 0
            ? round(($used / $distance) * 100, 2)
            : 0;

        $log = $rows->map(fn($r) => [
            'date'    => $r->prev_date,
            'product' => $r->product,
            'amount'  => $r->volume,
            'summa'   => $r->summa,
            'driver'  => $r->driver,
        ]);

        return response()->json([
            'automarka'      => $first->automarka,
            'carno'          => $first->carno,
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
            'log'            => $log->values()->toArray(),
        ]);
    }

    public function fetchAllForPeriod($month, $year)
    {
        $cars = Report::whereMonth('prev_date', $month)
            ->whereYear('prev_date', $year)
            ->distinct()
            ->pluck('carno');

        $reports = $cars->map(fn($car) =>
            json_decode($this->fetchDataFromLocal($car, $month, $year)->getContent(), true)
        )->filter()->values();

        return response()->json($reports);
    }

    public function getAvailableData()
    {
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