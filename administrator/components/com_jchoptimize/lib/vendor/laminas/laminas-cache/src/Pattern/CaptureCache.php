<?php

namespace _JchOptimizeVendor\Laminas\Cache\Pattern;

use _JchOptimizeVendor\Laminas\Cache\Exception;
use _JchOptimizeVendor\Laminas\Stdlib\ErrorHandler;

use function mkdir;
use function umask;

class CaptureCache extends AbstractPattern
{
    /**
     * Start the cache.
     *
     * @param string $pageId Page identifier
     */
    public function start($pageId = null)
    {
        if (null === $pageId) {
            $pageId = $this->detectPageId();
        }
        \ob_start(function ($content) use ($pageId) {
            $this->set($content, $pageId);
            // http://php.net/manual/function.ob-start.php
            // -> If output_callback  returns FALSE original input is sent to the browser.
            return \false;
        });
        \ob_implicit_flush(0);
    }

    /**
     * Write content to page identity.
     *
     * @param string      $content
     * @param null|string $pageId
     *
     * @throws Exception\LogicException
     */
    public function set($content, $pageId = null)
    {
        $publicDir = $this->getOptions()->getPublicDir();
        if (null === $publicDir) {
            throw new Exception\LogicException("Option 'public_dir' no set");
        }
        if (null === $pageId) {
            $pageId = $this->detectPageId();
        }
        $path = $this->pageId2Path($pageId);
        $file = $path.\DIRECTORY_SEPARATOR.$this->pageId2Filename($pageId);
        $this->createDirectoryStructure($publicDir.\DIRECTORY_SEPARATOR.$path);
        $this->putFileContent($publicDir.\DIRECTORY_SEPARATOR.$file, $content);
    }

    /**
     * Get from cache.
     *
     * @param null|string $pageId
     *
     * @return null|string
     *
     * @throws Exception\LogicException
     * @throws Exception\RuntimeException
     */
    public function get($pageId = null)
    {
        $publicDir = $this->getOptions()->getPublicDir();
        if (null === $publicDir) {
            throw new Exception\LogicException("Option 'public_dir' no set");
        }
        if (null === $pageId) {
            $pageId = $this->detectPageId();
        }
        $file = $publicDir.\DIRECTORY_SEPARATOR.$this->pageId2Path($pageId).\DIRECTORY_SEPARATOR.$this->pageId2Filename($pageId);
        if (\file_exists($file)) {
            ErrorHandler::start();
            $content = \file_get_contents($file);
            $error = ErrorHandler::stop();
            if (\false === $content) {
                throw new Exception\RuntimeException("Failed to read cached pageId '{$pageId}'", 0, $error);
            }

            return $content;
        }
    }

    /**
     * Checks if a cache with given id exists.
     *
     * @param null|string $pageId
     *
     * @return bool
     *
     * @throws Exception\LogicException
     */
    public function has($pageId = null)
    {
        $publicDir = $this->getOptions()->getPublicDir();
        if (null === $publicDir) {
            throw new Exception\LogicException("Option 'public_dir' no set");
        }
        if (null === $pageId) {
            $pageId = $this->detectPageId();
        }
        $file = $publicDir.\DIRECTORY_SEPARATOR.$this->pageId2Path($pageId).\DIRECTORY_SEPARATOR.$this->pageId2Filename($pageId);

        return \file_exists($file);
    }

    /**
     * Remove from cache.
     *
     * @param null|string $pageId
     *
     * @return bool
     *
     * @throws Exception\LogicException
     * @throws Exception\RuntimeException
     */
    public function remove($pageId = null)
    {
        $publicDir = $this->getOptions()->getPublicDir();
        if (null === $publicDir) {
            throw new Exception\LogicException("Option 'public_dir' no set");
        }
        if (null === $pageId) {
            $pageId = $this->detectPageId();
        }
        $file = $publicDir.\DIRECTORY_SEPARATOR.$this->pageId2Path($pageId).\DIRECTORY_SEPARATOR.$this->pageId2Filename($pageId);
        if (\file_exists($file)) {
            ErrorHandler::start();
            $res = \unlink($file);
            $err = ErrorHandler::stop();
            if (!$res) {
                throw new Exception\RuntimeException("Failed to remove cached pageId '{$pageId}'", 0, $err);
            }

            return \true;
        }

        return \false;
    }

    /**
     * Clear cached pages matching glob pattern.
     *
     * @param string $pattern
     *
     * @throws Exception\LogicException
     */
    public function clearByGlob($pattern = '**')
    {
        $publicDir = $this->getOptions()->getPublicDir();
        if (null === $publicDir) {
            throw new Exception\LogicException("Option 'public_dir' no set");
        }
        $it = new \GlobIterator($publicDir.'/'.$pattern, \GlobIterator::CURRENT_AS_SELF | \GlobIterator::SKIP_DOTS | \GlobIterator::UNIX_PATHS);
        foreach ($it as $pathname => $entry) {
            if ($entry->isFile()) {
                \unlink($pathname);
            }
        }
    }

    /**
     * Returns the generated file name.
     *
     * @param null|string $pageId
     *
     * @return string
     */
    public function getFilename($pageId = null)
    {
        if (null === $pageId) {
            $pageId = $this->detectPageId();
        }
        $publicDir = $this->getOptions()->getPublicDir();
        $path = $this->pageId2Path($pageId);
        $file = $path.\DIRECTORY_SEPARATOR.$this->pageId2Filename($pageId);

        return $publicDir.$file;
    }

    /**
     * Determine the page to save from the request.
     *
     * @return string
     *
     * @throws Exception\RuntimeException
     */
    protected function detectPageId()
    {
        if (!isset($_SERVER['REQUEST_URI'])) {
            throw new Exception\RuntimeException("Can't auto-detect current page identity");
        }

        return $_SERVER['REQUEST_URI'];
    }

    /**
     * Get filename for page id.
     *
     * @param string $pageId
     *
     * @return string
     */
    protected function pageId2Filename($pageId)
    {
        if ('/' === \substr($pageId, -1)) {
            return $this->getOptions()->getIndexFilename();
        }

        return \basename($pageId);
    }

    /**
     * Get path for page id.
     *
     * @param string $pageId
     *
     * @return string
     */
    protected function pageId2Path($pageId)
    {
        if ('/' === \substr($pageId, -1)) {
            $path = \rtrim($pageId, '/');
        } else {
            $path = \dirname($pageId);
        }
        // convert requested "/" to the valid local directory separator
        if (\DIRECTORY_SEPARATOR !== '/') {
            $path = \str_replace('/', \DIRECTORY_SEPARATOR, $path);
        }

        return $path;
    }

    /**
     * Write content to a file.
     *
     * @param string $file File complete path
     * @param string $data Data to write
     *
     * @throws Exception\RuntimeException
     */
    protected function putFileContent($file, $data)
    {
        $options = $this->getOptions();
        $locking = $options->getFileLocking();
        $perm = $options->getFilePermission();
        $umask = $options->getUmask();
        if (\false !== $umask && \false !== $perm) {
            $perm &= ~$umask;
        }
        ErrorHandler::start();
        $umask = \false !== $umask ? \umask($umask) : \false;
        $rs = \file_put_contents($file, $data, $locking ? \LOCK_EX : 0);
        if ($umask) {
            \umask($umask);
        }
        if (\false === $rs) {
            $err = ErrorHandler::stop();

            throw new Exception\RuntimeException("Error writing file '{$file}'", 0, $err);
        }
        if (\false !== $perm && !\chmod($file, $perm)) {
            $oct = \decoct($perm);
            $err = ErrorHandler::stop();

            throw new Exception\RuntimeException("chmod('{$file}', 0{$oct}) failed", 0, $err);
        }
        ErrorHandler::stop();
    }

    /**
     * Creates directory if not already done.
     *
     * @param string $pathname
     *
     * @throws Exception\RuntimeException
     */
    protected function createDirectoryStructure($pathname)
    {
        // Directory structure already exists
        if (\file_exists($pathname)) {
            return;
        }
        $options = $this->getOptions();
        $perm = $options->getDirPermission();
        $umask = $options->getUmask();
        if (\false !== $umask && \false !== $perm) {
            $perm &= ~$umask;
        }
        ErrorHandler::start();
        if (\false === $perm) {
            // built-in mkdir function is enough
            $umask = \false !== $umask ? \umask($umask) : \false;
            $res = \mkdir($pathname, \false !== $perm ? $perm : 0775, \true);
            if (\false !== $umask) {
                \umask($umask);
            }
            if (!$res) {
                $oct = \false === $perm ? '775' : \decoct($perm);
                $err = ErrorHandler::stop();

                throw new Exception\RuntimeException("mkdir('{$pathname}', 0{$oct}, true) failed", 0, $err);
            }
            if (\false !== $perm && !\chmod($pathname, $perm)) {
                $oct = \decoct($perm);
                $err = ErrorHandler::stop();

                throw new Exception\RuntimeException("chmod('{$pathname}', 0{$oct}) failed", 0, $err);
            }
        } else {
            // built-in mkdir function sets permission together with current umask
            // which doesn't work well on multo threaded webservers
            // -> create directories one by one and set permissions
            // find existing path and missing path parts
            $parts = [];
            $path = $pathname;
            while (!\file_exists($path)) {
                \array_unshift($parts, \basename($path));
                $nextPath = \dirname($path);
                if ($nextPath === $path) {
                    break;
                }
                $path = $nextPath;
            }
            // make all missing path parts
            foreach ($parts as $part) {
                $path .= \DIRECTORY_SEPARATOR.$part;
                // create a single directory, set and reset umask immediately
                $umask = \false !== $umask ? \umask($umask) : \false;
                $res = \mkdir($path, \false === $perm ? 0775 : $perm, \false);
                if (\false !== $umask) {
                    \umask($umask);
                }
                if (!$res) {
                    $oct = \false === $perm ? '775' : \decoct($perm);
                    ErrorHandler::stop();

                    throw new Exception\RuntimeException("mkdir('{$path}', 0{$oct}, false) failed");
                }
                if (\false !== $perm && !\chmod($path, $perm)) {
                    $oct = \decoct($perm);
                    ErrorHandler::stop();

                    throw new Exception\RuntimeException("chmod('{$path}', 0{$oct}) failed");
                }
            }
        }
        ErrorHandler::stop();
    }
}
