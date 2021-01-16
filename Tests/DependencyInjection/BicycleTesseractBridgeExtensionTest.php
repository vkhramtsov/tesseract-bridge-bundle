<?php

namespace Bicycle\TesseractBridgeBundle\Tests\DependencyInjection;

use Bicycle\Tesseract\Bridge\Configuration;
use Bicycle\TesseractBridgeBundle\DependencyInjection\BicycleTesseractBridgeExtension;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection;

class BicycleTesseractBridgeExtensionTest extends TestCase
{
    /** @var BicycleTesseractBridgeExtension */
    private BicycleTesseractBridgeExtension $testInstance;

    /** @var DependencyInjection\ContainerBuilder|MockObject */
    private $containerMock;

    /** @var DependencyInjection\Definition|MockObject */
    private $serviceDefMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->testInstance = new BicycleTesseractBridgeExtension();
        $this->containerMock = $this
            ->getMockBuilder(DependencyInjection\ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->serviceDefMock = $this
            ->getMockBuilder(DependencyInjection\Definition::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return array
     */
    public function incorrectConfigDataProvider(): array
    {
        return [
            [
                [],
                '/^The child ' .
                    '(config "integrations" under)|(node "integrations" at path)' .
                    ' "bicycle_tesseract_bridge"| must be configured\.$/',
            ],
            [
                ['integrations' => []],
                '/^The child ' .
                    '(config "integrations" under)|(node "integrations" at path)' .
                    ' "bicycle_tesseract_bridge" must be configured\.$/',
            ],
            [
                ['bicycle_tesseract_bridge' => []],
                '/^The child ' .
                    '(config "integrations" under)|(node "integrations" at path)' .
                    ' "bicycle_tesseract_bridge" must be configured\.$/',
            ],
            [
                ['bicycle_tesseract_bridge' => ['integrations' => []]],
                '/^Invalid configuration for path "bicycle_tesseract_bridge\.integrations": ' .
                    'At least one integration must be enabled$/',
            ],
            [
                ['bicycle_tesseract_bridge' => ['integrations' => ['cli' => [], 'ffi' => []]]],
                '/^Invalid configuration for path "bicycle_tesseract_bridge\.integrations": ' .
                    'Enabled integrations must have configured path$/',
            ],
            [
                ['bicycle_tesseract_bridge' => ['integrations' => ['cli' => ['path' => 'test'], 'ffi' => []]]],
                '/^Invalid configuration for path "bicycle_tesseract_bridge\.integrations": ' .
                    'Enabled integrations must have configured path$/',
            ],
        ];
    }

    /**
     * @dataProvider incorrectConfigDataProvider
     *
     * @param array  $configuration
     * @param string $exceptionMessage
     */
    public function testIncorrectConfiguration(array $configuration, string $exceptionMessage): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches($exceptionMessage);
        $this->testInstance->load($configuration, $this->containerMock);
    }

    public function testLoad(): void
    {
        $cliPath = 'cli test path';
        $ffiPath = 'ffi test path';

        $this
            ->serviceDefMock
            ->expects(self::once())
            ->method('replaceArgument')
            ->with(0, ['binary_path' => $cliPath, 'library_path' => $ffiPath]);

        $this->containerMock->expects(self::once())->method('fileExists')->willReturn(false);
        $this->containerMock
            ->expects(self::once())
            ->method('getDefinition')
            ->with(Configuration::class)
            ->willReturn($this->serviceDefMock);

        self::assertInstanceOf(DependencyInjection\Extension\Extension::class, $this->testInstance);
        $config = [
            'bicycle_tesseract_bridge' => [
                'integrations' => [
                    'cli' => [
                            'enabled' => true,
                            'path' => $cliPath,
                        ],
                    'ffi' => [
                            'enabled' => true,
                            'path' => $ffiPath,
                        ],
                ],
            ],
        ];
        $this->testInstance->load($config, $this->containerMock);
    }
}
