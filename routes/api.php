<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LogController;
use App\Http\Middleware\CheckAdminRole;
use App\Http\Middleware\CheckAuth;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Middleware\LogActions;

Route::group(["prefix" => "/docs"], function() {
    // if env debug is false return 404
    Route::get("", function() {

        if(env('APP_DEBUG', false) === false) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        // return public/docs/openapi.yml
        return response()->file(public_path('docs/openapi.yaml'));
    });
});


Route::group(['middleware' => [LogActions::class, 'api']], function(){

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

    Route::prefix("auth")->group(function() {
        Route::post("register", [AuthController::class, "register"])->name("api.auth.register");
        Route::post("login", [AuthController::class, "login"])->name("api.auth.login");
        Route::post("logout", [AuthController::class, "logout"])->name("api.auth.logout")->middleware(CheckAuth::class);

        Route::get("me", [AuthController::class, "me"])->name("api.auth.me")->middleware(CheckAuth::class);
        Route::get("deleteMe", [AuthController::class, "deleteMe"])->name("api.auth.deleteme")->middleware(CheckAuth::class);
    });

    Route::group(['middleware' => [CheckAuth::class, CheckAdminRole::class], 'prefix' => 'admin'], function() {
        // Route::post("assign", [PermissionController::class, "assignAdminRole"])->name("api.admin.assignAdminRole");
        // Route::post("remove", [PermissionController::class, "removeAdminRole"])->name("api.admin.removeAdminRole");
        // Route::get("user", [AdminController::class, "getUser"])->name("api.admin.getUser");
        // Route::get("users", [AdminController::class, "getUsers"])->name("api.admin.getUsers");
        // Route::delete("user/{id}", [AdminController::class, "deleteUser"])->name("api.admin.deleteUser");
        // Route::post("email", [AdminController::class, "changeEmail"])->name("api.admin.changeEmail");

        Route::group(['prefix' => "user"], function() {
            Route::post("", [AdminController::class, "createUser"])->name("api.admin.user.create");
            Route::get("", [AdminController::class, "getUsers"])->name("api.admin.user.gets");
            Route::get("{id}", [AdminController::class, "getUser"])->name("api.admin.user.get");
            Route::put("{id}", [AdminController::class, "updateUser"])->name("api.admin.user.update");
            Route::delete("{id}", [AdminController::class, "deleteUser"])->name("api.admin.user.delete");

            Route::post("{id}/assign", [AdminController::class, "assignAdminRole"])->name("api.admin.user.assignAdminRole");
            Route::post("{id}/remove", [AdminController::class, "removeAdminRole"])->name("api.admin.user.removeAdminRole");
        });



        Route::get("logs", [LogController::class, "index"])->name("api.admin.logs");
    });

    Route::group(['middleware' => [CheckAuth::class], 'prefix' => 'user'], function() {
        Route::put("email", [UserController::class, "changeEmail"])->name("api.user.change.email");
        Route::put("name", [UserController::class, "changeName"])->name("api.user.change.name");
        Route::put("password", [UserController::class, "changePassword"])->name("api.user.change.password");
        // Route::delete("", [UserController::class, "deleteUser"])->name("api.user.delete");
    });


    Route::get('parking_lots/{date}', [BookingController::class, 'getParkingLots'])->name('bookings.get');

});

