<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\AmocrmController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\OperatorController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\BigreportController;
use App\Http\Controllers\PbxBot\PbxBotController;
use App\Http\Controllers\Admin\TablereportController;
use App\Http\Controllers\Admin\ProductController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::redirect('/home','/');

Route::get('/', [UserController::class, 'redirect'])->name('redirect');

Route::get('feedback/{id}', [FeedbackController::class, 'index']);
Route::post('feedback/store', [FeedbackController::class, 'store'])->name('feedback.store');
Route::post('feedback/afterStore', [FeedbackController::class, 'afterStore'])->name('feedback.afterStore');

Route::post('mainProcess', [AmocrmController::class, 'mainProcess']);
Route::post('pbxBot', [PbxBotController::class, 'send']);
// ----------------
// Route::get('auth', [AmocrmController::class, 'getMonitoringCalls']);
// ----------------
Route::get('/logout', [UserController::class, 'logout'])->name('logout');

Route::group(['middleware' => 'guest'], function () {
    Route::get('/login', [UserController::class, 'login'])->name('login');
    Route::post('/login_store', [UserController::class, 'login_store'])->name('login.store');
});

Route::group(['prefix' => 'admin', 'middleware' => 'admin'], function () {
    Route::resource('operators', 'OperatorController');
    Route::resource('likes', 'LikeController');
    Route::resource('trainings', 'TrainingController');
    Route::resource('products', 'ProductController');
    Route::resource('scores', 'ScoreController');
    Route::resource('exceptions', 'ExceptionController');
    Route::resource('requestTypes', 'RequestTypeController');

    Route::get('tablereport', [TablereportController::class, 'index'])->name('admin.tablereport');
    Route::get('bigreport', [BigreportController::class, 'index'])->name('admin.bigreport');
    Route::get('piece', [BigreportController::class, 'piece'])->name('admin.piece');
    Route::get('bigreport/extra', [BigreportController::class, 'extra']);
    Route::get('bigreport/live', [BigreportController::class, 'live'])->name('admin.live');;

    Route::get('feedback/all', [FeedbackController::class, 'all'])->name('feedback.all');
    Route::get('profile/{id}', [UserController::class, 'profile'])->name('admin.profile');
    Route::put('profile_save', [UserController::class, 'profile_save'])->name('admin.profile_save');
    Route::get('report', [ReportController::class, 'index'])->name('admin.report');

    Route::get('score', [ReportController::class, 'score']);

    Route::get('monitoring', [ReportController::class, 'monitoring'])->name('admin.monitoring');
    Route::get('monitoring/data', [ReportController::class, 'monitoringData']);
    Route::get('monitoring/bigData', [ReportController::class, 'monitoringBigData']);

    Route::get('monitoring/operatorCondition', [ReportController::class, 'monitoringOperatorCondition']);
    Route::get('monitoring/operatorTime', [ReportController::class, 'monitoringOperatorTime']);
    Route::get('monitoring/unknownClients', [ReportController::class, 'monitoringUnknownClients']);

    Route::get('monitoring/users', [ReportController::class, 'monitoringUsers']);
    Route::get('monitoring/usersFeedbacks', [ReportController::class, 'monitoringUsersFeedbacks']);
    Route::get('monitoring/usersTrainings', [ReportController::class, 'monitoringusersTrainings']);
    Route::get('monitoring/personalMissed', [ReportController::class, 'monitoringPersonalMissed']);

    Route::get('monitoring/worklyData', [ReportController::class, 'worklyData']);
    Route::get('monitoring/worklySchedule', [ReportController::class, 'worklySchedule']);
    Route::get('monitoring/worklyOperators', [ReportController::class, 'worklyOperators']);
});

Route::group(['prefix' => 'operator', 'middleware' => 'operator'], function () {
    Route::get('bigreport', [BigreportController::class, 'operator'])->name('operator.bigreport');
    Route::get('bigreport/extra', [BigreportController::class, 'extra']);
    Route::get('score', [ReportController::class, 'score']);

    Route::get('monitoring', [ReportController::class, 'monitoring'])->name('monitoring');
    Route::get('monitoring/data', [ReportController::class, 'monitoringData']);
    Route::get('monitoring/bigData', [ReportController::class, 'monitoringBigData']);

    Route::get('monitoring/operatorCondition', [ReportController::class, 'monitoringOperatorCondition']);
    Route::get('monitoring/operatorTime', [ReportController::class, 'monitoringOperatorTime']);
    Route::get('monitoring/unknownClients', [ReportController::class, 'monitoringUnknownClients']);

    Route::get('monitoring/users', [ReportController::class, 'monitoringUsers']);
    Route::get('monitoring/usersFeedbacks', [ReportController::class, 'monitoringUsersFeedbacks']);
    Route::get('monitoring/usersTrainings', [ReportController::class, 'monitoringusersTrainings']);
    Route::get('monitoring/personalMissed', [ReportController::class, 'monitoringPersonalMissed']);

    Route::get('monitoring/worklyData', [ReportController::class, 'worklyData']);
    Route::get('monitoring/worklySchedule', [ReportController::class, 'worklySchedule']);
    Route::get('monitoring/worklyOperators', [ReportController::class, 'worklyOperators']);

    Route::get('tablereport', [TablereportController::class, 'index'])->name('operator.tablereport');
    Route::get('productoper', [ProductController::class, 'index'])->name('operator.products');
    
    //Route::resource('products', 'ProductController')->name('operator.product');

    // Route::get('monitoring', [UserController::class, 'monitoring'])->name('monitoring');
});

