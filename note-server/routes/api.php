<?php

use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\RegisterController;
use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\URL;

Route::post('/register', [RegisterController::class, 'register']);


Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = User::findOrFail($id);

    if (! hash_equals((string)$hash, sha1($user->getEmailForVerification()))) {
        abort(403, 'Ungültiger Verifizierungslink.');
    }

    if (! URL::hasValidSignature($request)) {
        abort(403, 'Signatur ungültig oder abgelaufen.');
    }

    if ($user->hasVerifiedEmail()) {
        return redirect(env('APP_FRONTEND_URL') . '/login?verified=1');
    }

    $user->markEmailAsVerified();

    return redirect(env('APP_FRONTEND_URL') . '/login?verified=1');
})->name('verification.verify');


Route::post('/password/email', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.reset-email');
Route::post('/password/reset', [PasswordResetController::class, 'reset'])->name('password.reset');

Route::middleware(['auth:api'])->group(function () {
    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', 'Verifizierungslink wurde gesendet!');
    })->name('verification.send');
})->middleware('throttle:4,1');

Route::middleware('auth:api')->get('/me', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:api')->post('/logout', function (Request $request) {
    $request->user()->currentAccessToken()->delete();
    return response()->json(['message' => 'Erfolgreich abgemeldet.']);
});



Route::prefix('page')->middleware('auth:api')->group(function () {
    Route::get('{uuidSlug}', [PageController::class, 'showByUuidSlug']);
    Route::put('{uuidSlug}', [PageController::class, 'update']);
    Route::patch('{uuidSlug}', [PageController::class, 'update']);
    Route::post('/', [PageController::class, 'store']);
    Route::delete('{uuidSlug}', [PageController::class, 'destroy']);
    Route::post('{page}/move', [PageController::class, 'move']);
    Route::post('{page}/icon', [PageController::class, 'changeIcon']);
});

Route::middleware('auth:api')->group(function () {
    // Favoriten
    Route::get('favorites',          [FavoriteController::class, 'index']);   // optional
    Route::post('page/{uuidSlug}/favorite',   [FavoriteController::class, 'store']);
    Route::delete('page/{uuidSlug}/favorite', [FavoriteController::class, 'destroy']);
});


Route::middleware('auth:api')->get('/pages', [App\Http\Controllers\PageController::class, 'list']);
