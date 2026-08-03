<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Sanctum only treats a request as stateful (session-backed) when its
        // Referer/Origin matches a configured stateful domain. Without this,
        // every feature test silently skips that path — which is exactly how
        // the BMI 419 bug and Breeze's own auth tests calling
        // $request->session() slipped through: Pest never sends this header
        // unless told to.
        $this->withHeader('Referer', config('app.frontend_url'));
    }
}
