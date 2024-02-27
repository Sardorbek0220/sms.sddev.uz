<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\AmocrmController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\OperatorController;
use App\Http\Controllers\Admin\ReportController;

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

Route::get('/logout', [UserController::class, 'logout'])->name('logout');

Route::group(['middleware' => 'guest'], function () {
    Route::get('/login', [UserController::class, 'login'])->name('login');
    Route::post('/login_store', [UserController::class, 'login_store'])->name('login.store');
});

Route::group(['prefix' => 'admin', 'middleware' => 'admin'], function () {
    Route::resource('operators', 'OperatorController');
    Route::get('feedback/all', [FeedbackController::class, 'all'])->name('feedback.all');
    Route::get('profile/{id}', [UserController::class, 'profile'])->name('admin.profile');
    Route::put('profile_save', [UserController::class, 'profile_save'])->name('admin.profile_save');
    Route::get('report', [ReportController::class, 'index'])->name('admin.report');
    // Route::get('test', [AmocrmController::class, 'test']);
    // Route::get('bot', [FeedbackController::class, 'bot']);
});

Route::group(['prefix' => 'operator', 'middleware' => 'operator'], function () {
    Route::get('monitoring', [UserController::class, 'monitoring'])->name('monitoring');
});

