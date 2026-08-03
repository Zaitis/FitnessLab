<?php

use App\Domain\Disclaimers\DisclaimerText;

it('holds the three disclaimer strings', function () {
    $disclaimer = new DisclaimerText(
        short: 'short text',
        standard: 'standard text',
        extended: 'extended text',
    );

    expect($disclaimer->short)->toBe('short text')
        ->and($disclaimer->standard)->toBe('standard text')
        ->and($disclaimer->extended)->toBe('extended text');
});
