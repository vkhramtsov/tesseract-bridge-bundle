<?php

namespace Bicycle\TesseractBridgeBundle\Tests;

use Bicycle\TesseractBridgeBundle\BicycleTesseractBridgeBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class BicycleTesseractBridgeBundleTest extends TestCase
{
    public function testExtends(): void
    {
        $testInstance = $this
            ->getMockBuilder(BicycleTesseractBridgeBundle::class)
            ->disableOriginalConstructor()
            ->getMock();
        self::assertInstanceOf(Bundle::class, $testInstance);
    }
}
