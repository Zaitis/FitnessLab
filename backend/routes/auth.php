<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

// No 'guest' middleware here: it's a server-side concept (redirect an
// already-authenticated visitor away from the login page) that doesn't
// apply to a decoupled API with no Blade views. Laravel's default
// RedirectIfAuthenticated falls back to redirecting to '/' when it can't
// find a named 'dashboard' or 'home' route — which we never register,
// since the dashboard is a frontend-only SPA route — and that fallback
// target isn't covered by our CORS config, so the fetch() call fails
// outright instead of returning a JSON response.
Route::post('/register', [RegisteredUserController::class, 'store'])
    ->name('register');

Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->name('login');

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->name('password.email');

Route::post('/reset-password', [NewPasswordController::class, 'store'])
    ->name('password.store');

// No 'auth' here either, for the same reason as above: this link is opened
// from an email client, essentially never in the browser session the user
// registered in. The signed URL plus the controller's hash check already
// tie the request to one specific user's email.
Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');
