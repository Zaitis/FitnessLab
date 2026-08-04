<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class PasswordResetLinkController extends Controller
{
    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        Password::sendResetLink($request->only('email'));

        // Deliberately the same response whether or not that address has an
        // account. Breeze ships this endpoint returning a 422 for an unknown
        // email, which turns it into a free user-enumeration oracle: anyone
        // could ask "does this person have a FitnessLab account?" one address
        // at a time. The user-facing copy is unchanged either way — "if that
        // address has an account, a link is on its way" — so nothing is lost
        // but the leak.
        return response()->json(['status' => __(Password::RESET_LINK_SENT)]);
    }
}
