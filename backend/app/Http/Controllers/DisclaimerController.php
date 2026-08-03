<?php

namespace App\Http\Controllers;

use App\Domain\Disclaimers\DisclaimerText;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

final class DisclaimerController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $locale = $this->resolveLocale($request);

        $payload = Cache::remember(
            "disclaimer.{$locale}",
            now()->addDay(),
            fn () => $this->payloadFor($locale),
        );

        return response()->json($payload);
    }

    private function resolveLocale(Request $request): string
    {
        $supported = config('supported_locales.supported');
        $requested = $request->query('locale');

        return in_array($requested, $supported, true)
            ? $requested
            : config('supported_locales.default');
    }

    /**
     * @return array{short: string, standard: string, extended: string}
     */
    private function payloadFor(string $locale): array
    {
        $strings = config('disclaimer');

        $disclaimer = new DisclaimerText(
            short: $strings['short'][$locale],
            standard: $strings['standard'][$locale],
            extended: $strings['extended'][$locale],
        );

        return [
            'short' => $disclaimer->short,
            'standard' => $disclaimer->standard,
            'extended' => $disclaimer->extended,
        ];
    }
}
