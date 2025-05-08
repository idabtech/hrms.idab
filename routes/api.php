<?php

use App\Http\Controllers\Api\JobController;
use App\Http\Resources\jobDetailsResources;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Job;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('jobdetails', [JobController::class, 'getJobDetails']);
// Route::post('create-token', [JobController::class, 'createToken']);