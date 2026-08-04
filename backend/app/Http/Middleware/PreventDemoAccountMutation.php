<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The demo account's credentials are public (see config/demo.php and the
 * README), so the first visitor to change its password or delete it would
 * lock out every visitor after them. Applied only to destructive
 * self-service actions (password, email, account deletion) — see
 * docs/ARCHITECTURE.md's "demo account" section for what this deliberately
 * does not restrict (e.g. the locale switcher).
 */
class PreventDemoAccountMutation
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->is_demo) {
            return response()->json([
                'message' => 'This is the shared demo account and cannot be modified. Register your own free account to save changes.',
            ], 403);
        }

        return $next($request);
    }
}
