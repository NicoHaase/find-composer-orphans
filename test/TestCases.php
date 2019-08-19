<?php

use PHPUnit\Framework\TestCase;

include_once '../ComposerOrphanChecker.php';

class TestCases extends TestCase
{
    public function testEmptyCase()
    {
        $checker = new ComposerOrphanChecker(__DIR__ . '/files/empty.json', __DIR__ . '/files/empty.lock');
        $this->assertEmpty($checker->getOrphans());
    }

    public function testSimpleCase()
    {
        $checker = new ComposerOrphanChecker(__DIR__ . '/files/simple.json', __DIR__ . '/files/simple.lock');

        $expectedOrphans = ['doctrine/instantiator', 'symfony/http-kernel'];
        $this->assertEqualsCanonicalizing($expectedOrphans, $checker->getOrphans());
    }
}
