<?php

namespace Skies\Bundle\ImagineBundle\Templating;

use Skies\Bundle\ImagineBundle\Imagine\CachePathResolver;
use Symfony\Component\Filesystem\Filesystem;
use Twig_Extension;
use Twig_SimpleFunction;

class ImagineExtension extends Twig_Extension
{
    /**
     * @var Skies\Bundle\ImagineBundle\Imagine\CachePathResolver
     */
    private $cachePathResolver;

    /**
     * Constructs by setting $cachePathResolver
     *
     * @param Skies\Bundle\ImagineBundle\Imagine\CachePathResolver $cachePathResolver
     */
    public function __construct(CachePathResolver $cachePathResolver)
    {
        $this->cachePathResolver = $cachePathResolver;
    }

    /**
     * (non-PHPdoc)
     * @see Twig_Extension::getFilters()
     */
    public function getFilters()
    {
        return array(
        		'apply_filter' => new \Twig_SimpleFilter('apply_filter', array($this, 'applyFilter') ));
        
    }

    /**
     * Gets cache path of an image to be filtered
     *
     * @param string $path
     * @param string $filter
     * @param boolean $absolute
     *
     * @return string
     */
    public function applyFilter($path, $filter, $absolute = false)
    {
        return $this->cachePathResolver->getBrowserPath($path, $filter, $absolute);
    }

    /**
     * (non-PHPdoc)
     * @see Twig_ExtensionInterface::getName()
     */
    public function getName()
    {
        return 'imagine';
    }
}
