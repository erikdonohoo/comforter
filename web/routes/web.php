<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
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

Route::get('/{any}', function (Request $request) {

    // See if request is for static asset
    $path = resource_path() . '/comforter/' . $request->path();

    if (File::isFile($path)) {
        return response()->file($path);
    }

    return response()->file(resource_path() . '/comforter/index.html');
})
    ->where('any', '.*')
    ->middleware('gitlab.auth');
