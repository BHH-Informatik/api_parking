<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    return response()->json([
        'success' => true,
        'service' => env('APP_NAME', 'api_parking'),
        'version' => env('APP_VERSION', '0.0.1'),
        'stage' => env('APP_STAGE', 'development'),
        'health' => env('APP_MAINTENANCE', false) ? 'false' : 'ok',
        'ip' => $request->header()['x-forwarded-for'] ?? 'unknown',
    ], 200);
})->name('api.index');


Route::post("/dev", function (Request $request) {
    // $params = $request->validate([
    //     'name' => 'required|string',
    // ]);

    return response()->json([
        'success' => true,
        'message' => 'Hello ',
    ], 200);
});
