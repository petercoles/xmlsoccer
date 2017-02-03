<?php

namespace Tests\XmlSoccer;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    public function assertStringContains($expected, $actual)
    {
        $this->assertRegexp("/$expected/", $actual);
    }
}
