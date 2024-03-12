<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanyStateController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\DriverLogController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\SmartcardController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VehicleStateController;
use App\Http\Controllers\VehicleTypeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\CheckAccessLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware(['auth:sanctum','checkaccesslevel:1'])->get('/user', function (Request $request) {
    return $request->user();
});

// Guest
// Login Logoutski
Route::post('/login', [AuthController::class,'login']);
Route::post('/logout', [AuthController::class,'logout']);

Route::post('/password/email', [ForgotPasswordController::class,'sendResetLinkEmail']);
Route::post('/password/reset', [ResetPasswordController::class,'reset'])->name('password.reset');
/*
// Temporal
Route::apiResource('/conductores', DriverController::class);
Route::apiResource('/empresas', CompanyController::class);
Route::apiResource('/vehiculos', VehicleController::class);
Route::apiResource('/smartcards', SmartcardController::class);
Route::apiResource('/dispositivos', DeviceController::class);
Route::apiResource('/historial', DriverLogController::class);
Route::apiResource('/eventos', EventController::class);

// Estados
Route::apiResource('/estadoempresas', CompanyStateController::class);
Route::apiResource('/estadovehiculos', VehicleStateController::class);
Route::apiResource('/tipovehiculos', VehicleTypeController::class);
*/
// Solo sesion
Route::middleware(['auth:sanctum','checkaccesslevel:1'])->group(function () {
    // Tablas Principales
    Route::apiResource('/conductores', DriverController::class);
    Route::apiResource('/smartcards', SmartcardController::class);
    Route::apiResource('/empresas', CompanyController::class);
    Route::apiResource('/vehiculos', VehicleController::class);
    Route::apiResource('/dispositivos', DeviceController::class);
    Route::apiResource('/historial', DriverLogController::class);
    Route::apiResource('/eventos', EventController::class);

    // Estados
    Route::apiResource('/estadoempresas', CompanyStateController::class);
    Route::apiResource('/estadovehiculos', VehicleStateController::class);
    Route::apiResource('/tipovehiculos', VehicleTypeController::class);
});

Route::middleware(['auth:sanctum','checkaccesslevel:5'])->group(function () {
    Route::get('/users', [UserController::class,'index']);
    Route::get('/users/{id}', [UserController::class,'show']);
    Route::post('/users', [UserController::class,'register']);
    Route::put('/users/{id}', [UserController::class,'adminUpdate']);
});