<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class VerifyEmailController extends Controller
{
    /**
     * Mark the email address identified by the signed URL as verified.
     *
     * No 'auth' middleware on this route: the link is opened from an email
     * client, essentially never in the same browser session the user
     * registered in. The signed URL (validated by the 'signed' middleware)
     * plus this hash check already tie the request to one specific user's
     * email, so a prior Sanctum session isn't needed for the security
     * guarantee — only for the (wrong) assumption that it exists.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $user = User::find($request->route('id'));

        if (! $user || ! hash_equals(sha1($user->getEmailForVerification()), (string) $request->route('hash'))) {
            throw new NotFoundHttpException;
        }

        if (! $user->hasVerifiedEmail() && $user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect(config('app.frontend_url').'/dashboard?verified=1');
    }
}
