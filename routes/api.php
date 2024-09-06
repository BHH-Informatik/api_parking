<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {

    $dbHealth = false;
    try {
        \DB::connection()->getPDO();
        \DB::connection()->getDatabaseName();
        $dbHealth = true;
    } catch (\Exception $e) {
        $dbHealth = false;
    }
    return response()->json([
        'success' => true,
        'service' => env('APP_NAME', 'api_parking'),
        'version' => env('APP_VERSION', '0.0.1'),
        'stage' => env('APP_STAGE', 'development'),
        'health' => $dbHealth ? 'ok' : 'false',
        'ip' => $request->header()['x-forwarded-for'] ?? 'unknown',
    ], 200);
})->name('api.index');

Route::get("/health", function (Request $request) {

    $dbHealth = false;
    try {
        \DB::connection()->getPDO();
        \DB::connection()->getDatabaseName();
    } catch (\Exception $e) {
        return response()->json(['health' => false, 'module' => 'DB'], 500);
    }
    return response()->json([
        'success' => true,
        'message' => 'ok',
    ], 200);
})->name('api.health');


Route::post("/dev", function (Request $request) {
    $params = $request->validate([
        'name' => 'required|string',
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Hello, ' . $params['name'],
    ], 200);
});
