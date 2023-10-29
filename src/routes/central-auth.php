<?php

use App\Http\Controllers\Central\Auth\AADOAuthController;
use App\Http\Controllers\Central\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Central\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Central\Auth\ConfirmableWebAuthnController;
use App\Http\Controllers\Central\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Central\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Central\Auth\NewPasswordController;
use App\Http\Controllers\Central\Auth\PasswordResetLinkController;
use App\Http\Controllers\Central\Auth\RegisteredUserController;
use App\Http\Controllers\Central\Auth\VerifyEmailController;
use App\Http\Controllers\Central\Auth\WebAuthnLoginController;
use Illuminate\Support\Facades\Route;

Route::name('central.')->group(function () {
    Route::get('tenant/auth', [AADOAuthController::class, 'start'])
        ->name('aad.start');
    Route::get('tenant/verify', [AADOAuthController::class, 'verify'])
        ->name('aad.verify');

    Route::middleware('guest:central')->group(function () {
        Route::get('register', [RegisteredUserController::class, 'create'])
            ->name('register');

        Route::post('register', [RegisteredUserController::class, 'store']);

        Route::get('login', [AuthenticatedSessionController::class, 'create'])
            ->name('login');

        Route::post('login', [AuthenticatedSessionController::class, 'store']);

        Route::get('two-factor-challenge', [AuthenticatedSessionController::class, 'check'])
            ->name('two_factor');

        Route::post('two-factor-challenge/backup', [AuthenticatedSessionController::class, 'resend'])
            ->name('two_factor.resend');

        Route::post('two-factor-challenge', [AuthenticatedSessionController::class, 'confirm']);

        Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
            ->name('password.request');

        Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
            ->name('password.email');

        Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
            ->name('password.reset');

        Route::post('reset-password', [NewPasswordController::class, 'store'])
            ->name('password.update');

        Route::post('webauthn/verify', [WebAuthnLoginController::class, 'verify'])->name('webauthn.verify');
        Route::post('webauthn/challenge', [WebAuthnLoginController::class, 'challenge'])->name('webauthn.challenge');
    });

    Route::middleware('auth:central')->group(function () {
        Route::get('verify-email', [EmailVerificationPromptController::class, '__invoke'])
            ->name('verification.notice');

        Route::get('verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
            ->middleware(['signed', 'throttle:6,1'])
            ->name('verification.verify');

        Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
            ->middleware('throttle:6,1')
            ->name('verification.send');

        Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
            ->name('password.confirm');

        Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

        Route::get('confirm-password/oauth', [ConfirmablePasswordController::class, 'oauth'])
            ->name('confirm-password.oauth');

        Route::get('confirm-password/oauth-verify', [ConfirmablePasswordController::class, 'verifyOauth'])
            ->name('confirm-password.oauth-verify');

        Route::post('confirm-password/webauthn/verify', [ConfirmableWebAuthnController::class, 'verify'])
            ->name('confirm-password.webauthn.verify');
        Route::post('confirm-password/webauthn/challenge', [ConfirmableWebAuthnController::class, 'challenge'])
            ->name('confirm-password.webauthn.challenge');

        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
            ->name('logout');
    });
});
