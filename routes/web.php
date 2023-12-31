<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WondeClientController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/getTeachersIds', [WondeClientController::class, 'getTeachersIds']);

Route::get('/getTeachersWeeklyTimeTable/{teachersId}', [WondeClientController::class, 'getTeachersWeeklyTimeTable']);