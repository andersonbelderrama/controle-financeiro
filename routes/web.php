<?php

use Illuminate\Support\Facades\Route;

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

//index
Route::get('/', 'App\Http\Controllers\HomeController@index')->name('home');

//resources
Route::resource('descriptions', 'App\Http\Controllers\DescriptionReleaseController');
Route::resource('payment_inputs', 'App\Http\Controllers\PaymentInputController');
Route::resource('payment_outputs', 'App\Http\Controllers\PaymentOutputController');




//retorno datatables
Route::get('descriptions-data', 'App\Http\Controllers\DescriptionReleaseController@loadData')->name('descriptions.data');
Route::get('payment_inputs-data', 'App\Http\Controllers\PaymentInputController@loadData')->name('payment_inputs.data');
Route::get('payment_outputs-data', 'App\Http\Controllers\PaymentOutputController@loadData')->name('payment_outputs.data');
