<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;


Route::prefix("auth")->group(function() {
    Route::post("register", [AuthController::class, "register"])->name("api.auth.register");
    Route::post("login", [AuthController::class, "login"])->name("api.auth.login");
    Route::post("logout", [AuthController::class, "logout"])->name("api.auth.logout");
});



