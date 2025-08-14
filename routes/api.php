<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ReportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('monitoring/keys', [ReportController::class, 'monitoringKeys']);
Route::get('monitoring/users', [ReportController::class, 'monitoringUsers']);
Route::get('monitoring/data', [ReportController::class, 'monitoringData']);
Route::get('monitoring/bigData', [ReportController::class, 'monitoringBigData']);
Route::get('monitoring/operatorCondition', [ReportController::class, 'monitoringOperatorCondition']);
Route::get('monitoring/operatorTime', [ReportController::class, 'monitoringOperatorTime']);
