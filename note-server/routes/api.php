<?php

use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use GuzzleHttp\Client;
use App\Http\Controllers\PageController;

Route::middleware('auth:api')->get('/me', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:api')->post('/logout', function (Request $request) {
    $request->user()->currentAccessToken()->delete();
    return response()->json(['message' => 'Erfolgreich abgemeldet.']);
});



Route::prefix('page')->middleware('auth:api')->group(function () {
    Route::get('{uuidSlug}', [PageController::class, 'showByUuidSlug']);
    //Route::get('{page}', [PageController::class, 'show']);
    Route::post('/', [PageController::class, 'store']);
    Route::delete('{page}', [PageController::class, 'destroy']);
    Route::post('{page}/move', [PageController::class, 'move']);
    Route::post('{page}/icon', [PageController::class, 'changeIcon']);
});
