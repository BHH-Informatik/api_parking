<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\CheckAdminRole;
use App\Http\Middleware\CheckAuth;
use Illuminate\Support\Facades\Route;


Route::prefix("auth")->group(function() {
    Route::post("register", [AuthController::class, "register"])->name("api.auth.register");
    Route::post("login", [AuthController::class, "login"])->name("api.auth.login");
    Route::post("logout", [AuthController::class, "logout"])->name("api.auth.logout")->middleware(CheckAuth::class);
});

Route::group(['middleware' => [CheckAuth::class, CheckAdminRole::class], 'prefix' => 'admin'], function() {
    Route::post("assign", [PermissionController::class, "assignAdminRole"])->name("api.admin.assignAdminRole");
    Route::post("remove", [PermissionController::class, "removeAdminRole"])->name("api.admin.removeAdminRole");
    Route::get("user", [AdminController::class, "getUser"])->name("api.admin.getUser");
    Route::get("users", [AdminController::class, "getUsers"])->name("api.admin.getUsers");
    Route::delete("user", [AdminController::class, "deleteUser"])->name("api.admin.deleteUser");
    Route::post("email", [AdminController::class, "changeEmail"])->name("api.admin.changeEmail");
});

Route::group(['middleware' => [CheckAuth::class], 'prefix' => 'user'], function() {
    Route::post("email", [UserController::class, "changeEmail"])->name("api.user.change.email");
    Route::post("name", [UserController::class, "changeName"])->name("api.user.change.name");
    Route::post("password", [UserController::class, "changePassword"])->name("api.user.change.password");
    Route::delete("", [UserController::class, "deleteUser"])->name("api.user.delete");
});

