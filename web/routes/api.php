<?php

use App\Http\Controllers\CoverageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::group([
    'middleware' => []
], function () {
    // TODO: Remove this old redundant route after rebuilding comforter-cli
    Route::post('apps/{app}/coverage', [CoverageController::class, 'addCommit']);

    Route::post('commits', [CoverageController::class, 'addCommit']);
    Route::get('apps/{app}', [CoverageController::class, 'getApp']);
    Route::get('apps', [CoverageController::class, 'getApps']);
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
