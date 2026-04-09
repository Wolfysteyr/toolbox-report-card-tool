<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'prev_date',
        'periods',
        'volume',
        'prev_volume',
        'summa',
        'mileage',
        'prev_mileage',
        'mileage_consumption',
        'product',
        'carno',
        'driver',
        'bakas_tilpums',
        'paterins',
        'motora_tilpums',
        'automarka',
        'atbildigais'
    ];

}
