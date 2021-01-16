<?php

namespace Bicycle\TesseractBridgeBundle\DependencyInjection;

use Bicycle\Tesseract\Bridge;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection;

class BicycleTesseractBridgeExtension extends DependencyInjection\Extension\Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, DependencyInjection\ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new DependencyInjection\Loader\YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('tesseract_bridge.yaml');

        $arguments = [];
        /** @psalm-suppress MixedArrayAccess */
        if ($config['integrations']['cli']['enabled']) {
            /** @psalm-suppress MixedArrayAccess */
            $arguments['binary_path'] = (string) $config['integrations']['cli']['path'];
        }

        /** @psalm-suppress MixedArrayAccess */
        if ($config['integrations']['ffi']['enabled']) {
            /** @psalm-suppress MixedArrayAccess */
            $arguments['library_path'] = (string) $config['integrations']['ffi']['path'];
        }

        $definition = $container->getDefinition(Bridge\Configuration::class);
        $definition->replaceArgument(0, $arguments);
    }
}
