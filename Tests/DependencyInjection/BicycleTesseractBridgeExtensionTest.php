<?php

namespace Bicycle\TesseractBridgeBundle\Tests\DependencyInjection;

use Bicycle\Tesseract\Bridge;
use Bicycle\TesseractBridgeBundle\DataCollector\TesseractBridgeDataCollector;
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

        $bridgeCliDefMock = clone $this->serviceDefMock;
        $bridgeFfiDefMock = clone $this->serviceDefMock;

        $collectorDefMock = clone $this->serviceDefMock;
        $collectorDefMock
            ->expects(self::exactly(4))
            ->method('replaceArgument')
            ->withConsecutive([2, $config['bicycle_tesseract_bridge']], [0, $bridgeCliDefMock], [1, $bridgeFfiDefMock]);

        $this
            ->serviceDefMock
            ->expects(self::once())
            ->method('replaceArgument')
            ->with(0, ['binary_path' => $cliPath, 'library_path' => $ffiPath]);

        $this->containerMock->expects(self::once())->method('fileExists')->willReturn(false);
        $this->containerMock
            ->expects(self::exactly(4))
            ->method('getDefinition')
            ->withConsecutive(
                [TesseractBridgeDataCollector::class],
                ['bicycle.tesseract_bridge.integrations.cli'],
                ['bicycle.tesseract_bridge.integrations.ffi'],
                [Bridge\Configuration::class]
            )
            ->willReturnOnConsecutiveCalls(
                $collectorDefMock,
                $bridgeCliDefMock,
                $bridgeFfiDefMock,
                $this->serviceDefMock
            );

        self::assertInstanceOf(DependencyInjection\Extension\Extension::class, $this->testInstance);
        $this->testInstance->load($config, $this->containerMock);
    }

    public function testLoadOnlyCliEnabled(): void
    {
        $cliPath = 'cli test path';
        $config = [
            'bicycle_tesseract_bridge' => [
                'integrations' => [
                    'cli' => [
                        'enabled' => true,
                        'path' => $cliPath,
                    ],
                    'ffi' => [
                        'enabled' => false,
                        'path' => null,
                    ],
                ],
            ],
        ];

        $bridgeCliDefMock = clone $this->serviceDefMock;

        $collectorDefMock = clone $this->serviceDefMock;
        $collectorDefMock
            ->expects(self::exactly(3))
            ->method('replaceArgument')
            ->withConsecutive([2, $config['bicycle_tesseract_bridge']], [0, $bridgeCliDefMock]);

        $this
            ->serviceDefMock
            ->expects(self::once())
            ->method('replaceArgument')
            ->with(0, ['binary_path' => $cliPath]);

        $this->containerMock->expects(self::once())->method('fileExists')->willReturn(false);
        $this->containerMock
            ->expects(self::exactly(3))
            ->method('getDefinition')
            ->withConsecutive(
                [TesseractBridgeDataCollector::class],
                ['bicycle.tesseract_bridge.integrations.cli'],
                [Bridge\Configuration::class]
            )
            ->willReturnOnConsecutiveCalls(
                $collectorDefMock,
                $bridgeCliDefMock,
                $this->serviceDefMock
            );
        $this
            ->containerMock
            ->expects(self::once())
            ->method('removeDefinition')
            ->with('bicycle.tesseract_bridge.integrations.ffi');

        self::assertInstanceOf(DependencyInjection\Extension\Extension::class, $this->testInstance);
        $this->testInstance->load($config, $this->containerMock);
    }

    public function testLoadOnlyFfiEnabled(): void
    {
        $ffiPath = 'ffi test path';
        $config = [
            'bicycle_tesseract_bridge' => [
                'integrations' => [
                    'cli' => [
                        'enabled' => false,
                        'path' => null,
                    ],
                    'ffi' => [
                        'enabled' => true,
                        'path' => $ffiPath,
                    ],
                ],
            ],
        ];

        $bridgeFfiDefMock = clone $this->serviceDefMock;

        $collectorDefMock = clone $this->serviceDefMock;
        $collectorDefMock
            ->expects(self::exactly(3))
            ->method('replaceArgument')
            ->withConsecutive([2, $config['bicycle_tesseract_bridge']], [1, $bridgeFfiDefMock]);

        $this
            ->serviceDefMock
            ->expects(self::once())
            ->method('replaceArgument')
            ->with(0, ['library_path' => $ffiPath]);

        $this->containerMock->expects(self::once())->method('fileExists')->willReturn(false);
        $this->containerMock
            ->expects(self::exactly(3))
            ->method('getDefinition')
            ->withConsecutive(
                [TesseractBridgeDataCollector::class],
                ['bicycle.tesseract_bridge.integrations.ffi'],
                [Bridge\Configuration::class]
            )
            ->willReturnOnConsecutiveCalls(
                $collectorDefMock,
                $bridgeFfiDefMock,
                $this->serviceDefMock
            );
        $this
            ->containerMock
            ->expects(self::once())
            ->method('removeDefinition')
            ->with('bicycle.tesseract_bridge.integrations.cli');

        self::assertInstanceOf(DependencyInjection\Extension\Extension::class, $this->testInstance);
        $this->testInstance->load($config, $this->containerMock);
    }
}
