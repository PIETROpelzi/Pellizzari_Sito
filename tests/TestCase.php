<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $compiledPath = storage_path('framework')
            .DIRECTORY_SEPARATOR.'testing'
            .DIRECTORY_SEPARATOR.'views';

        if (! is_dir($compiledPath)) {
            mkdir($compiledPath, 0755, true);
        }

        config()->set('view.compiled', $compiledPath);
    }
}
