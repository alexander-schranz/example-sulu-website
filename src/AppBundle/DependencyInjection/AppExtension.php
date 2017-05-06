<?php

namespace AppBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This class loads and manages the configuration for this bundle.
 */
class AppExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        if ($container->hasExtension('framework')) {
            if (!$container->hasParameter('asset_build_version')) {
                $version = $this->getPackageVersion();
                $container->setParameter('asset_build_version', $version);
            }

            $debug = $container->getParameter('kernel.debug');

            $container->prependExtensionConfig(
                'framework',
                [
                    'assets' => [
                        'packages' => [
                            'static' => [
                                'version' => $container->getParameter('asset_build_version'),
                                'version_format' => ($debug ? '%%s' : '%%s?v=%%s'),
                            ],
                        ],
                    ],
                ]
            );
        }
    }

    /**
     * Get package version.
     *
     * @return string
     */
    private function getPackageVersion()
    {
        $version = '0.0.0';
        $packageJsonPath = __DIR__ . '/../package.json';

        if (file_exists($packageJsonPath)) {
            $packageJson = json_decode(file_get_contents($packageJsonPath), true);
            $version = $packageJson['version'];
        }

        return $version;
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');
    }
}
