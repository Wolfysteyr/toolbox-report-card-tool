<?php

use App\Http\Controllers\ReportController;
use App\Models\Report;

it('does not inflate odo end for a first-ever single entry without previous mileage', function () {
    Report::create([
        'dated' => '2026-05-10',
        'periods' => '2026-05-01',
        'volume' => 100,
        'prev_volume' => 0,
        'summa' => 0,
        'mileage' => 1000,
        'prev_mileage' => 0,
        'mileage_consumption' => null,
        'product' => 'DĪZEĻDEGVIELA',
        'carno' => 'ONE001',
        'driver' => 'TEST DRIVER',
        'bakas_tilpums' => 60,
        'paterins' => 10,
        'motora_tilpums' => 1200,
        'automarka' => 'TEST CAR',
        'atbildigais' => 'TEST MANAGER',
    ]);

    $response = app(ReportController::class)->fetchDataFromLocal('ONE001', 5, 2026);
    $payload = $response->getData(true);

    expect($payload['odo_end'])->toBe(1000)
        ->and($payload['distance'])->toBe(0);
});

it('uses relative mileage when the first row has no previous baseline but there are multiple entries', function () {
    Report::create([
        'dated' => '2026-05-05',
        'periods' => '2026-05-01',
        'volume' => 80,
        'prev_volume' => 0,
        'summa' => 0,
        'mileage' => 1000,
        'prev_mileage' => 0,
        'mileage_consumption' => null,
        'product' => 'DĪZEĻDEGVIELA',
        'carno' => 'ONE001',
        'driver' => 'TEST DRIVER',
        'bakas_tilpums' => 60,
        'paterins' => 10,
        'motora_tilpums' => 1200,
        'automarka' => 'TEST CAR',
        'atbildigais' => 'TEST MANAGER',
    ]);

    Report::create([
        'dated' => '2026-05-20',
        'periods' => '2026-05-01',
        'volume' => 70,
        'prev_volume' => 80,
        'summa' => 0,
        'mileage' => 1200,
        'prev_mileage' => 1000,
        'mileage_consumption' => 200,
        'product' => 'DĪZEĻDEGVIELA',
        'carno' => 'ONE001',
        'driver' => 'TEST DRIVER',
        'bakas_tilpums' => 60,
        'paterins' => 10,
        'motora_tilpums' => 1200,
        'automarka' => 'TEST CAR',
        'atbildigais' => 'TEST MANAGER',
    ]);

    $response = app(ReportController::class)->fetchDataFromLocal('ONE001', 5, 2026);
    $payload = $response->getData(true);

    expect($payload['distance'])->toBe(200)
        ->and($payload['odo_end'])->toBe(1310);
});
