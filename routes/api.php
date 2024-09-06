<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PermissionController;
use App\Http\Middleware\CheckAdminRole;
use App\Http\Middleware\CheckAuth;
use Illuminate\Support\Facades\Route;


Route::prefix("auth")->group(function() {
    Route::post("register", [AuthController::class, "register"])->name("api.auth.register");
    Route::post("login", [AuthController::class, "login"])->name("api.auth.login");
    Route::get("logout", [AuthController::class, "logout"])->name("api.auth.logout")->middleware(CheckAuth::class);
});

Route::group(['middleware' => [CheckAuth::class, CheckAdminRole::class], 'prefix' => 'admin'], function() {
    Route::post("assign", [PermissionController::class, "assignAdminRole"])->name("api.admin.assignAdminRole");
    Route::post("remove", [PermissionController::class, "removeAdminRole"])->name("api.admin.removeAdminRole");
});

