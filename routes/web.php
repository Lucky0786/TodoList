<?php

use Illuminate\Support\Facades\Route;


use App\Http\Controllers\HomeController;
use App\Http\Controllers\TaskController;
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

Route::GET('/', function () {
    return view('welcome');
});
Auth::routes();
Route::get('/home', [App\Http\Controllers\TaskController::class, 'index'])->name('home');

Route::post('/tasks/create', [App\Http\Controllers\TaskController::class, 'store']);
Route::put('/tasks/{task}', [App\Http\Controllers\TaskController::class, 'update']);
Route::delete('/tasks/{task}', [App\Http\Controllers\TaskController::class, 'destroy']);


