<?php

namespace Skies\Bundle\ImagineBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SkiesImagineExtension extends Extension
{
    /**
     * @see Symfony\Component\DependencyInjection\Extension.ExtensionInterface::load()
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('imagine.xml');

        $config = $this->mergeConfig($configs);

        $driver = 'gd';

        if (isset($config['driver'])) {
            $driver = strtolower($config['driver']);
        }

        if (!in_array($driver, array('gd', 'imagick', 'gmagick'))) {
            throw new \InvalidArgumentException('Invalid imagine driver specified');
        }

        $container->setAlias('imagine', new Alias('imagine.'.$driver));

        foreach (array('cache_prefix', 'web_root', 'source_root', 'filters') as $key) {
            if (isset($config[$key])) {
                $container->setParameter('imagine.'.$key, $config[$key]);
            }
        }
    }

    private function mergeConfig(array $configs)
    {
        $config = array();

        foreach ($configs as $cnf) {
            $config = array_merge_recursive($config, $cnf);
        }

        return $config;
    }

    /**
     * @see Symfony\Component\DependencyInjection\Extension.ExtensionInterface::getAlias()
     * @codeCoverageIgnore
     */
    function getAlias()
    {
        return 'skies_imagine';
    }
}
