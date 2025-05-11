<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnalizeContractsController;

Route::get('/', function () {
    return view('main');
});

Route::get('/smart_contract_analize/{smartAddress}', [AnalizeContractsController::class, 'analizeContractCode']);

Route::post('/getGraph/{smartAddress}', [AnalizeContractsController::class, 'getGraphAnalize']);

Route::get('/getGraph/{smartAddress}', [AnalizeContractsController::class, 'getGraphAnalize']);

Route::get('/test', [AnalizeContractsController::class, 'test']);

Route::get('/subscribe/{user_id}/{smartAddress}', [AnalizeContractsController::class, 'subscibeContract']);

Route::get('/subscribedContracts/{user_id}', [AnalizeContractsController::class, 'getSubContracts']);

Route::get('/test2', function () {
    return view('welcome');
});

Route::get('/getNotifications/{user_id}', [AnalizeContractsController::class, 'getNotifications']);
