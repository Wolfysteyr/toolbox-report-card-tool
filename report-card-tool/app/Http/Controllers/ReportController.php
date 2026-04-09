<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;

class ReportController extends Controller
{

    public function copy(){
        // This method will handle the copying of data from the external database to the local database

        // this is where it will call the Porter API and get the data

        $data = $this->fetchDataFromPorter();
        $data = json_decode($data, true); // assuming the data is in JSON format
        // now we will loop through the data and save it to the local database
        foreach($data as $item){
            // here we will create a new Report model and save the data to the local database
            $report = new Report();
            $report->prev_date = $item['prev_date'];
            $report->periods = $item['periods'];
            $report->volume = $item['volume'];
            $report->prev_volume = $item['prev_volume'];
            $report->summa = $item['summa'];
            $report->mileage = $item['mileage'];
            $report->prev_mileage = $item['prev_mileage'];
            $report->mileage_consumption = $item['mileage_consumption'];
            $report->product = $item['product'];
            $report->carno = $item['carno'];
            $report->driver = $item['driver'];
            $report->bakas_tilpums = $item['bakas_tilpums'];
            $report->paterins = $item['paterins'];
            $report->motora_tilpums = $item['motora_tilpums'];
            $report->automarka = $item['automarka'];
            $report->atbildigais = $item['atbildigais'];
            $report->save();
        }
    }

    public function fetchDataFromPorter(){
        // This method will handle the actual fetching of data from the Porter API

        // this is where you will make the API call to Porter and return the data
    }

    public function fetchDataFromLocal($car, $month, $year){
        // This method will handle fetching data from the local database and returning it to the frontend
        $reports = Report::where('carno', $car)
                        ->whereMonth('prev_date', $month)
                        ->whereYear('prev_date', $year)
                        ->get();
        return response()->json($reports);
    }

    public function getAvailableData(){
        // This method will return the available cars, months, and years for the dropdowns in the frontend
        $cars = Report::select('carno')->distinct()->get();
        $months = Report::selectRaw('MONTH(prev_date) as month')->distinct()->get();
        $years = Report::selectRaw('YEAR(prev_date) as year')->distinct()->get();
        return response()->json([
            'cars' => $cars,
            'months' => $months,
            'years' => $years
        ]);
    }
}
