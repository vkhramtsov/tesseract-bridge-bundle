<?php

namespace Bicycle\TesseractBridgeBundle\Tests\DataCollector;

use Bicycle\Tesseract\Bridge;
use Bicycle\TesseractBridgeBundle\DataCollector\TesseractBridgeDataCollector;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TesseractBridgeDataCollectorTest extends TestCase
{
    /** @var Request|MockObject */
    private Request $requestMock;

    /** @var Response|MockObject */
    private Response $responseMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->requestMock = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $this->requestMock->expects(self::never())->method(self::anything());
        $this->responseMock = $this->getMockBuilder(Response::class)->disableOriginalConstructor()->getMock();
        $this->responseMock->expects(self::never())->method(self::anything());
    }

    public function testCollectEmptyConfiguration(): void
    {
        $testInstance = new TesseractBridgeDataCollector(null, null, []);
        $testInstance->collect($this->requestMock, $this->responseMock);
        self::assertEquals(TesseractBridgeDataCollector::NAME, $testInstance->getName());
        self::assertEquals(extension_loaded('ffi'), $testInstance->hasFFI());
        self::assertFalse($testInstance->isCliIntegrationEnabled());
        self::assertFalse($testInstance->isFFIIntegrationEnabled());
        self::assertEquals([], $testInstance->getCliIntegrationData());
        self::assertEquals([], $testInstance->getFfiIntegrationData());
        $testInstance->reset();
        self::assertFalse($testInstance->isCliIntegrationEnabled());
        self::assertFalse($testInstance->isFFIIntegrationEnabled());
        self::assertEquals([], $testInstance->getCliIntegrationData());
        self::assertEquals([], $testInstance->getFfiIntegrationData());
    }

    public function testCollectCliIntegrationEnabled(): void
    {
        $testCliVersion = 'test cli version';
        $testCliLanguages = ['lang1', 'lang2'];
        $configuration = ['integrations' => ['cli' => ['enabled' => true]]];
        $cliIntegrationMock = $this->getMockBuilder(Bridge\CLI::class)->disableOriginalConstructor()->getMock();
        $cliIntegrationMock->expects(self::once())->method('getVersion')->willReturn($testCliVersion);
        $cliIntegrationMock->expects(self::once())->method('getAvailableLanguages')->willReturn($testCliLanguages);
        $testInstance = new TesseractBridgeDataCollector($cliIntegrationMock, null, $configuration);
        $testInstance->collect($this->requestMock, $this->responseMock);
        self::assertTrue($testInstance->isCliIntegrationEnabled());
        self::assertFalse($testInstance->isFFIIntegrationEnabled());
        self::assertEquals(
            ['tesseractVersion' => $testCliVersion, 'availableLanguages' => $testCliLanguages],
            $testInstance->getCliIntegrationData()
        );
        self::assertEquals([], $testInstance->getFfiIntegrationData());
    }

    /**
     * @return \string[][]
     */
    public function tesseractVersionDataProvider(): array
    {
        return [
            ['test ffi version'],
            ['4.0.0'],
        ];
    }

    /**
     * @dataProvider tesseractVersionDataProvider
     *
     * @param string $testFfiVersion
     */
    public function testCollectFfiIntegrationEnabled(string $testFfiVersion): void
    {
        $testFfiLanguages = ['lang1', 'lang2'];
        $configuration = ['integrations' => ['ffi' => ['enabled' => true]]];
        $ffiIntegrationMock = $this->getMockBuilder(Bridge\FFI::class)->disableOriginalConstructor()->getMock();
        $ffiIntegrationMock->expects(self::once())->method('getVersion')->willReturn($testFfiVersion);
        $ffiIntegrationMock->expects(self::once())->method('getAvailableLanguages')->willReturn($testFfiLanguages);
        $testInstance = new TesseractBridgeDataCollector(null, $ffiIntegrationMock, $configuration);
        $testInstance->collect($this->requestMock, $this->responseMock);
        self::assertFalse($testInstance->isCliIntegrationEnabled());
        self::assertTrue($testInstance->isFFIIntegrationEnabled());
        self::assertEquals([], $testInstance->getCliIntegrationData());
        self::assertEquals(
            ['tesseractVersion' => $testFfiVersion, 'availableLanguages' => $testFfiLanguages],
            $testInstance->getFfiIntegrationData()
        );
    }
}
