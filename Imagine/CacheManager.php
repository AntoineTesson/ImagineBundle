<?php

namespace Skies\Bundle\ImagineBundle\Imagine;

use Skies\Bundle\ImagineBundle\Imagine\CachePathResolver;
use Skies\Bundle\ImagineBundle\Imagine\Filter\FilterManager;
use Imagine\Image\ImagineInterface;
use Symfony\Component\Filesystem\Filesystem;

class CacheManager
{
    /**
     * CacheManager constructor.
     *
     * @param CachePathResolver $cachePathResolver
     * @param ImagineInterface  $imagine
     * @param FilterManager     $filterManager
     * @param Filesystem        $filesystem
     * @param string            $webRoot
     * @param string            $sourceRoot
     * @param int               $permissions
     */
    public function __construct(
        CachePathResolver $cachePathResolver,
        ImagineInterface $imagine,
        FilterManager $filterManager,
        Filesystem $filesystem,
        $webRoot,
        $sourceRoot,
        $permissions
    )
    {
        $this->cachePathResolver = $cachePathResolver;
        $this->imagine           = $imagine;
        $this->filterManager     = $filterManager;
        $this->filesystem        = $filesystem;
        $this->webRoot           = $webRoot;
        $this->sourceRoot        = $sourceRoot;
        $this->permissions       = $permissions;
    }

    /**
     * Forces image caching and returns path to cached image.
     *
     * @param string $basePath
     * @param string $path
     * @param string $filter
     *
     * @return string|null
     */
    public function cacheImage($basePath, $path, $filter)
    {
        $path = '/'.ltrim($path, '/');

        //TODO: find out why I need double urldecode to get a valid path
        $browserPath = urldecode(urldecode($this->cachePathResolver->getBrowserPath($path, $filter)));

        if (!empty($basePath) && 0 === strpos($browserPath, $basePath)) {
             $browserPath = substr($browserPath, strlen($basePath));
        }

         // if cache path cannot be determined, return 404
        if (null === $browserPath) {
            return null;
        }

        $realPath = $this->webRoot.$browserPath;
        
        $sourcePathRoot = $this->filterManager->getOption($filter, "source_root", $this->sourceRoot);
        $sourcePath = $sourcePathRoot.$path;

        // if the file has already been cached, just return path
        if (is_file($realPath)) {
            return $realPath;
        }

        if (!is_file($sourcePath)) {
            return null;
        }

        $dir = pathinfo($realPath, PATHINFO_DIRNAME);

        if (!is_dir($dir)) {
            try {
                if (false === $this->filesystem->mkdir($dir)) {
                    throw new \RuntimeException(sprintf(
                        'Could not create directory %s', $dir
                    ));
                }
            } catch (\Exception $e) {
                if (!is_dir($dir)) {
                    throw $e;
                }
            }
        }

        // TODO: get rid of hard-coded quality and format
        $this->filterManager->getFilter($filter)
            ->apply($this->imagine->open($sourcePath))
            ->save($realPath, array(
                'quality' => $this->filterManager->getOption($filter, "quality", 100),
                'format'  => $this->filterManager->getOption($filter, "format", null)
            ))
        ;
        
        try {
            if (!chmod($realPath, $this->permissions))
            {
                throw new \RuntimeException(sprintf(
                    'Could not set permissions %s on image saved in %s', $this->permissions, $realPath
                ));
            }
            
        } catch (Exception $e) {
            throw $e;
        }
        
        return $realPath;
    }
}
