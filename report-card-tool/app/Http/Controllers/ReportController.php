<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;

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
        // TODO: implement Porter API call
        // return Http::get('http://porter.vtl.lv/api/template/...')->body();
        
        return null;
    }

    public function fetchDataFromLocal($car, $month, $year){
        $rows = Report::where('carno', $car)
            ->whereRaw("strftime('%m', prev_date) = ?", [str_pad($month, 2, '0', STR_PAD_LEFT)])
            ->whereRaw("strftime('%Y', prev_date) = ?", [(string)$year])
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

        $fuelStart = $first->prev_volume;
        $fuelEnd   = $last->volume;
        $received  = $rows->sum(fn($r) => $r->volume - $r->prev_volume);
        

        $used      = round($fuelStart + $received - $fuelEnd, 2);

        $factualCons = $distance > 0
            ? round(($used / $distance) * 100, 2)
            : 0;

        $periodDates = explode(' - ', $first->periods);

        $log = $rows->map(fn($r) => [
            'date'    => $r->prev_date,
            'product' => $r->product,
            'amount'  => round($r->volume - $r->prev_volume, 2),
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
            'period_start'   => $periodDates[0] ?? null,
            'period_end'     => $periodDates[1] ?? null,
            'odo_start'      => $odoStart,
            'odo_end'        => $odoEnd,
            'distance'       => $distance,
            'fuel_start'     => round($fuelStart, 2),
            'received'       => round($received, 2),
            'used'           => $used,
            'fuel_end'       => round($fuelEnd, 2),
            'factual_cons'   => $factualCons,
            'log'            => $log,
        ]);
    }

    public function getAvailableData()
    {
        $rows = Report::selectRaw("strftime('%Y', prev_date) as year, strftime('%m', prev_date) as month, carno")
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