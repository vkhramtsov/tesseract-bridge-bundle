<?php

namespace Bicycle\TesseractBridgeBundle\DataCollector;

use Bicycle\Tesseract\Bridge;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class TesseractBridgeDataCollector extends DataCollector
{
    /** @var string */
    public const NAME = 'tesseract_bridge';

    /** @var Bridge\CLI|null */
    private ?Bridge\CLI $cliIntegration;

    /** @var Bridge\FFI|null */
    private ?Bridge\FFI $ffiIntegration;

    /** @var array */
    private array $configuration;

    /**
     * @param Bridge\CLI|null $cliIntegration
     * @param Bridge\FFI|null $ffiIntegration
     * @param array           $configuration
     */
    public function __construct(?Bridge\CLI $cliIntegration, ?Bridge\FFI $ffiIntegration, array $configuration)
    {
        $this->cliIntegration = $cliIntegration;
        $this->ffiIntegration = $ffiIntegration;
        $this->configuration = $configuration;
    }

    /**
     * {@inheritDoc}
     */
    public function collect(Request $request, Response $response, \Throwable $exception = null): void
    {
        $this->data = ['configuration' => $this->configuration, 'cliIntegration' => [], 'ffiIntegration' => []];
        if ($this->cliIntegration instanceof Bridge\CLI) {
            $this->data['cliIntegration'] = [
                    'tesseractVersion' => $this->cliIntegration->getVersion(),
                    'availableLanguages' => $this->cliIntegration->getAvailableLanguages(),
                ];
        }
        if ($this->ffiIntegration instanceof Bridge\FFI) {
            /*
             * Here we have to do some magic because of bug in libtesseract 4.0.0. We cannot get languages because of
             * !strcmp(locale, "C"):Error:Assert failed:in file baseapi.cpp, line 209
             * Segmentation fault
             * Workaround is perform this magic with locales
             */
            $version = $this->ffiIntegration->getVersion();
            if ('4.0.0' === $version) {
                $oldLocale = setlocale(LC_ALL, 0);
                setlocale(LC_ALL, 'C');
            }
            $this->data['ffiIntegration'] = [
                'tesseractVersion' => $version,
                'availableLanguages' => $this->ffiIntegration->getAvailableLanguages(),
            ];
            if (isset($oldLocale)) { // Returning locale back to original state
                setlocale(LC_ALL, $oldLocale);
            }
        }
    }

    public function reset(): void
    {
        $this->data = [];
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return static::NAME;
    }

    /**
     * @return bool
     */
    public function hasFFI(): bool
    {
        return extension_loaded('ffi');
    }

    /**
     * @return bool
     */
    public function isCliIntegrationEnabled(): bool
    {
        return $this->data['configuration']['integrations']['cli']['enabled'] ?? false;
    }

    /**
     * @return bool
     */
    public function isFfiIntegrationEnabled(): bool
    {
        return $this->data['configuration']['integrations']['ffi']['enabled'] ?? false;
    }

    /**
     * @return array
     */
    public function getCliIntegrationData(): array
    {
        return $this->data['cliIntegration'] ?? [];
    }

    /**
     * @return array
     */
    public function getFfiIntegrationData(): array
    {
        return $this->data['ffiIntegration'] ?? [];
    }
}
