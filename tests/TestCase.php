<?php

namespace Tests\XmlSoccer;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    public function assertStringContains($expected, $actual)
    {
        $this->assertRegexp("/$expected/", $actual);
    }

    protected function getData($type)
    {
        return file_get_contents(__DIR__.'/data/truncated-'.$type.'.xml');
    }

    protected function apiExceptionResponse($message)
    {
        return '<?xml version="1.0" encoding="utf-8"?>'."\n".
               '<XMLSOCCER.COM>Blah, blah, '.$message.', blah, blah, blah</XMLSOCCER.COM>';
    }
}
