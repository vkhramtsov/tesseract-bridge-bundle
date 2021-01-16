<?php

namespace Bicycle\TesseractBridgeBundle\DependencyInjection;

use Symfony\Component\Config\Definition;

class Configuration implements Definition\ConfigurationInterface
{
    /**
     * @psalm-suppress PossiblyUndefinedMethod
     * @psalm-suppress MixedMethodCall
     *
     * @return Definition\Builder\TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new Definition\Builder\TreeBuilder('bicycle_tesseract_bridge');

        $treeBuilder
            ->getRootNode()
                ->children()
                    ->arrayNode('integrations')
                        ->isRequired()
                        ->children()
                            ->arrayNode('cli')
                                ->canBeEnabled()
                                ->children()
                                    ->scalarNode('path')
                                        ->defaultNull()
                                        ->example('tesseract')
                                        ->info('Path to tesseract cli binary. ' .
                                            'It could be just "tesseract" in case binary in PATH or relative/absolute')
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('ffi')
                                ->canBeEnabled()
                                ->children()
                                    ->scalarNode('path')
                                        ->defaultNull()
                                        ->example('libtesseract.so.4')
                                        ->info('Path to tesseract shared library. It could be' .
                                            'just "libtesseract.so.4" in case library is in PATH or relative/absolute')
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->validate()
                            ->ifTrue(\Closure::fromCallable([$this, 'hasEnabledIntegrations']))
                            ->thenInvalid('At least one integration must be enabled')
                        ->end()
                        ->validate()
                            ->ifTrue(\Closure::fromCallable([$this, 'hasCorrectlyEnabledIntegrations']))
                            ->thenInvalid('Enabled integrations must have configured path')
                        ->end()
                    ->end()
                ->end();

        return $treeBuilder;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     *
     * @param array $integrations
     *
     * @return bool
     */
    private function hasEnabledIntegrations(array $integrations): bool
    {
        /** @var array $integration */
        foreach ($integrations as $integration) {
            /** @psalm-suppress MixedArrayAccess */
            if ($integration['enabled']) {
                return false;
            }
        }

        return true;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     *
     * @param array $integrations
     *
     * @return bool
     */
    private function hasCorrectlyEnabledIntegrations(array $integrations): bool
    {
        /** @var array $integration */
        foreach ($integrations as $integration) {
            /** @psalm-suppress MixedArrayAccess */
            if ($integration['enabled'] && empty($integration['path'])) {
                return true;
            }
        }

        return false;
    }
}
