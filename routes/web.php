<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\AmocrmController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\OperatorController;

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

// Route::get('/', function () {
//     return view('welcome');
// });
Route::redirect('/','/login');

Route::get('feedback/{id}', [FeedbackController::class, 'index']);
Route::post('feedback/store', [FeedbackController::class, 'store'])->name('feedback.store');
Route::get('feedback/store/success', [FeedbackController::class, 'success'])->name('feedback.success');

// Route::get('sendSms', [FeedbackController::class, 'sendSms']);
Route::post('mainProcess', [AmocrmController::class, 'mainProcess']);

Route::get('/logout', [UserController::class, 'logout'])->name('logout');

Route::group(['middleware' => 'guest'], function () {
    Route::get('/login', [UserController::class, 'login'])->name('login');
    Route::post('/login_store', [UserController::class, 'login_store'])->name('login.store');
});

Route::group(['prefix' => 'admin', 'middleware' => 'admin'], function () {
    Route::resource('operators', 'OperatorController');
    Route::get('feedback/all', [FeedbackController::class, 'all'])->name('feedback.all');
    Route::get('profile', [UserController::class, 'profile'])->name('admin.profile');
    Route::put('profile_save', [UserController::class, 'profile_save'])->name('admin.profile_save');
    // Route::get('test', [AmocrmController::class, 'test']);
});

