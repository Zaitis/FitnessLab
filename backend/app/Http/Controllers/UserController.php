<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateLocaleRequest;
use Illuminate\Http\JsonResponse;

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
}
