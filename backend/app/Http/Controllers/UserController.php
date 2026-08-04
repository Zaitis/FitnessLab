<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\UpdateEmailRequest;
use App\Http\Requests\UpdateLocaleRequest;
use App\Http\Requests\UpdatePasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

final class UserController extends Controller
{
    /**
     * Persist the authenticated user's language choice, so backend-generated
     * content (plan text, disclaimers) matches what the language switcher
     * shows in the interface. Anonymous visitors' choice stays client-side
     * only (localStorage) — there's no account to attach it to.
     */
    public function updateLocale(UpdateLocaleRequest $request): JsonResponse
    {
        $request->user()->update(['locale' => $request->string('locale')->value()]);

        return response()->json(['locale' => $request->user()->locale]);
    }

    public function updatePassword(UpdatePasswordRequest $request): Response
    {
        $request->user()->update([
            'password' => Hash::make($request->string('password')->value()),
        ]);

        return response()->noContent();
    }

    /**
     * Changing the email address resets verification — the new address has
     * never had its ownership proven, regardless of whether the old one had.
     */
    public function updateEmail(UpdateEmailRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->forceFill([
            'email' => $request->string('email')->value(),
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();

        return response()->json(['email' => $user->email]);
    }

    public function destroy(DeleteAccountRequest $request): Response
    {
        $user = $request->user();

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $user->delete();

        return response()->noContent();
    }
}
