<?php

use Illuminate\Support\Facades\File;

// Named specifically (not `flattenKeys` or similar) because Pest loads every
// test file's top-level functions into one shared global namespace — a
// generic name here risks a "cannot redeclare" fatal against a future file.
function flattenTranslationKeysForParityTest(array $array, string $prefix = ''): array
{
    $keys = [];

    foreach ($array as $key => $value) {
        $path = $prefix === '' ? (string) $key : "{$prefix}.{$key}";

        if (is_array($value)) {
            $keys = [...$keys, ...flattenTranslationKeysForParityTest($value, $path)];
        } else {
            $keys[] = $path;
        }
    }

    sort($keys);

    return $keys;
}

it('exposes the same php translation keys in every supported locale', function () {
    $locales = config('supported_locales.supported');
    $reference = array_shift($locales);
    $referencePath = lang_path($reference);

    $files = collect(File::files($referencePath))->map(fn ($file) => $file->getFilename());

    expect($files)->not->toBeEmpty();

    foreach ($locales as $locale) {
        foreach ($files as $file) {
            $referenceKeys = flattenTranslationKeysForParityTest(require "{$referencePath}/{$file}");
            $localeKeys = flattenTranslationKeysForParityTest(require lang_path("{$locale}/{$file}"));

            expect($localeKeys)->toBe($referenceKeys, "Key mismatch in {$locale}/{$file}");
        }
    }
});

it('exposes the same json translation keys in every supported locale', function () {
    $locales = config('supported_locales.supported');
    $reference = array_shift($locales);

    $referenceKeys = collect(json_decode(File::get(lang_path("{$reference}.json")), true))
        ->keys()->sort()->values()->all();

    foreach ($locales as $locale) {
        $localeKeys = collect(json_decode(File::get(lang_path("{$locale}.json")), true))
            ->keys()->sort()->values()->all();

        expect($localeKeys)->toBe($referenceKeys, "Key mismatch in {$locale}.json");
    }
});
