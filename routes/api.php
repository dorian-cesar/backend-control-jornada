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
use App\Http\Controllers\MasGPSController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleLogController;
use App\Http\Controllers\WebSocketController;
use App\Http\Middleware\CheckAccessLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\RegistroController;

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
        Route::get('/sync',[VehicleController::class,'sync']);
        Route::get('/mibus',[VehicleController::class,'getMiBus']);
    Route::get('/logs',[VehicleLogController::class,'getLogsLatest']);
    Route::get('/logs/{id}',[VehicleLogController::class,'getLogsID']);
});

Route::middleware(['auth:sanctum','checkaccesslevel:5'])->group(function () {
    Route::get('/users', [UserController::class,'index']);
     Route::get('/users/{id}', [UserController::class,'show']);
     Route::post('/users', [UserController::class,'register']);
     Route::put('/users/{id}', [UserController::class,'adminUpdate']);
     Route::delete('/users/{id}', [UserController::class,'destroy']);

    
});

/*Route::middleware(['auth:sanctum','checkaccesslevel:10'])->group(function () {
    
});*/

Route::apiResource('/openconductores', DriverController::class);
Route::apiResource('/opensmartcards', SmartcardController::class);
Route::apiResource('/openempresas', CompanyController::class);
Route::apiResource('/openvehiculos', VehicleController::class);
Route::apiResource('/opendispositivos', DeviceController::class);
Route::apiResource('/openhistorial', DriverLogController::class);
Route::apiResource('/openeventos', EventController::class);

// Estados
Route::apiResource('/openestadoempresas', CompanyStateController::class);
Route::apiResource('/openestadovehiculos', VehicleStateController::class);
Route::apiResource('/opentipovehiculos', VehicleTypeController::class);

// Test
Route::get('/opensync',[VehicleController::class,'sync']);
Route::get('/openlogs',[VehicleLogController::class,'getLogsLatest']);
Route::get('/openlogs/{id}',[VehicleLogController::class,'getLogsID']);


Route::post('/alerta',[NotificationController::class, 'push']);
Route::get('/alerta', [NotificationController::class, 'index']);

Route::get('/wsbuses', [MasGPSController::class, 'getBuses']);
Route::get('/wsparaderos', [MasGPSController::class, 'getParaderos']);
Route::get('/wshash', [MasGPSController::class, 'getHash']);
Route::post('/wslog', [VehicleLogController::class, 'store']);

Route::get('/gpshist/{patente}', [MasGPSController::class, 'getHistory']);
Route::get('/gpslogs', [MasGPSController::class, 'getLogs']);
Route::get('/gpsalert', [MasGPSController::class, 'getSpeedAlert']);
Route::get('/gpsemploy', [MasGPSController::class, 'getEmployees']);
Route::get('/gpsusers', [UserController::class, 'getAll']);
Route::get('/gpsmarcar', [MasGPSController::class, 'getWorkHours']);

Route::apiResource('/registros', RegistroController::class);