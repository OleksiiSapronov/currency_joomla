<?php

declare(strict_types=1);

namespace _JchOptimizeVendor\Laminas\Cache\Storage\Adapter\Filesystem;

use _JchOptimizeVendor\Laminas\Cache\Exception\RuntimeException;
use _JchOptimizeVendor\Laminas\Cache\Storage\Adapter\Filesystem\Exception\MetadataException;
use _JchOptimizeVendor\Laminas\Cache\Storage\Adapter\Filesystem\Exception\UnlinkException;
use _JchOptimizeVendor\Laminas\Stdlib\ErrorHandler;

use function file_get_contents;
use function file_put_contents;

/**
 * @internal
 */
final class LocalFilesystemInteraction implements FilesystemInteractionInterface
{
    public function delete(string $file): bool
    {
        ErrorHandler::start();
        $res = @\unlink($file);
        $err = ErrorHandler::stop();
        if (!$res && \file_exists($file)) {
            \assert(null !== $err);

            throw new UnlinkException($file, $err);
        }

        return \true;
    }

    public function write(string $file, string $contents, ?int $umask, ?int $permissions, bool $lock, bool $block, ?bool &$wouldBlock): bool
    {
        $nonBlocking = $lock && $block;
        $wouldBlock = null;
        if (null !== $umask && null !== $permissions) {
            $permissions &= ~$umask;
        }
        ErrorHandler::start();
        // if locking and non blocking is enabled -> file_put_contents can't used
        if ($lock && $nonBlocking) {
            $umask = null !== $umask ? \umask($umask) : null;
            $fp = \fopen($file, 'cb');
            if ($umask) {
                \umask($umask);
            }
            if (!$fp) {
                $err = ErrorHandler::stop();

                throw new RuntimeException("Error opening file '{$file}'", 0, $err);
            }
            if (null !== $permissions && !\chmod($file, $permissions)) {
                \fclose($fp);
                $oct = \decoct($permissions);
                $err = ErrorHandler::stop();

                throw new RuntimeException("chmod('{$file}', 0{$oct}) failed", 0, $err);
            }
            $wouldblockFromFileLock = null;
            if (!\flock($fp, \LOCK_EX | \LOCK_NB, $wouldblockFromFileLock)) {
                \fclose($fp);
                $err = ErrorHandler::stop();
                if ($wouldblockFromFileLock) {
                    $wouldBlock = \true;

                    return \false;
                }

                throw new RuntimeException("Error locking file '{$file}'", 0, $err);
            }
            if (\false === \fwrite($fp, $contents)) {
                \flock($fp, \LOCK_UN);
                \fclose($fp);
                $err = ErrorHandler::stop();

                throw new RuntimeException("Error writing file '{$file}'", 0, $err);
            }
            if (!\ftruncate($fp, \strlen($contents))) {
                \flock($fp, \LOCK_UN);
                \fclose($fp);
                $err = ErrorHandler::stop();

                throw new RuntimeException("Error truncating file '{$file}'", 0, $err);
            }
            \flock($fp, \LOCK_UN);
            \fclose($fp);
            // else -> file_put_contents can be used
        } else {
            $flags = 0;
            if ($lock) {
                $flags |= \LOCK_EX;
            }
            $umask = null !== $umask ? \umask($umask) : null;
            $rs = \file_put_contents($file, $contents, $flags);
            if ($umask) {
                \umask($umask);
            }
            if (\false === $rs) {
                $err = ErrorHandler::stop();

                throw new RuntimeException("Error writing file '{$file}'", 0, $err);
            }
            if (null !== $permissions && !\chmod($file, $permissions)) {
                $oct = \decoct($permissions);
                $err = ErrorHandler::stop();

                throw new RuntimeException("chmod('{$file}', 0{$oct}) failed", 0, $err);
            }
        }
        ErrorHandler::stop();

        return \true;
    }

    public function read(string $file, bool $lock, bool $block, ?bool &$wouldBlock): string
    {
        $wouldBlock = null;
        ErrorHandler::start();
        // if file locking enabled -> file_get_contents can't be used
        if ($lock) {
            $fp = \fopen($file, 'rb');
            if (\false === $fp) {
                $err = ErrorHandler::stop();

                throw new RuntimeException("Error opening file '{$file}'", 0, $err);
            }
            if ($block) {
                $wouldblockFromFileLock = null;
                $locked = \flock($fp, \LOCK_SH | \LOCK_NB, $wouldblockFromFileLock);
                if ($wouldblockFromFileLock) {
                    \fclose($fp);
                    ErrorHandler::stop();
                    $wouldBlock = \true;

                    return '';
                }
            } else {
                $locked = \flock($fp, \LOCK_SH);
            }
            if (!$locked) {
                \fclose($fp);
                $err = ErrorHandler::stop();

                throw new RuntimeException("Error locking file '{$file}'", 0, $err);
            }
            $res = \stream_get_contents($fp);
            if (\false === $res) {
                \flock($fp, \LOCK_UN);
                \fclose($fp);
                $err = ErrorHandler::stop();

                throw new RuntimeException('Error getting stream contents', 0, $err);
            }
            \flock($fp, \LOCK_UN);
            \fclose($fp);
            // if file locking disabled -> file_get_contents can be used
        } else {
            $res = \file_get_contents($file, \false);
            if (\false === $res) {
                $err = ErrorHandler::stop();

                throw new RuntimeException("Error getting file contents for file '{$file}'", 0, $err);
            }
        }
        ErrorHandler::stop();

        return $res;
    }

    public function exists(string $file): bool
    {
        return \file_exists($file);
    }

    public function lastModifiedTime(string $file): int
    {
        ErrorHandler::start();
        $mtime = \filemtime($file);
        $error = ErrorHandler::stop();
        if (\false === $mtime) {
            \assert(null !== $error);

            throw new MetadataException(MetadataException::METADATA_MTIME, $error);
        }

        return $mtime;
    }

    public function lastAccessedTime(string $file): int
    {
        ErrorHandler::start();
        $atime = \fileatime($file);
        $error = ErrorHandler::stop();
        if (\false === $atime) {
            \assert(null !== $error);

            throw new MetadataException(MetadataException::METADATA_ATIME, $error);
        }

        return $atime;
    }

    public function createdTime(string $file): int
    {
        ErrorHandler::start();
        $ctime = \filectime($file);
        $error = ErrorHandler::stop();
        if (\false === $ctime) {
            \assert(null !== $error);

            throw new MetadataException(MetadataException::METADATA_CTIME, $error);
        }

        return $ctime;
    }

    public function filesize(string $file): int
    {
        ErrorHandler::start();
        $filesize = \filesize($file);
        $error = ErrorHandler::stop();
        if (\false === $filesize) {
            \assert(null !== $error);

            throw new MetadataException(MetadataException::METADATA_FILESIZE, $error);
        }

        return $filesize;
    }

    public function clearStatCache(): void
    {
        \clearstatcache();
    }

    public function availableBytes(string $directory): int
    {
        ErrorHandler::start();
        $bytes = \disk_free_space($directory);
        $error = ErrorHandler::stop();
        if (\false === $bytes) {
            throw new RuntimeException('Could not detect disk free space', 0, $error);
        }

        return (int) $bytes;
    }

    public function totalBytes(string $directory): int
    {
        ErrorHandler::start();
        $bytes = \disk_total_space($directory);
        $error = ErrorHandler::stop();
        if (\false === $bytes) {
            throw new RuntimeException('Could not detect disk total space', 0, $error);
        }

        return (int) $bytes;
    }

    public function touch(string $file): bool
    {
        ErrorHandler::start();
        $touch = \touch($file);
        $error = ErrorHandler::stop();
        if (!$touch) {
            throw new RuntimeException("Error touching file '{$file}'", 0, $error);
        }

        return \true;
    }

    public function umask(int $umask): int
    {
        return \umask($umask);
    }

    public function createDirectory(string $directory, int $permissions, bool $recursive = \false, ?int $umask = null): void
    {
        $umaskToRestore = null;
        if ($umask) {
            $umaskToRestore = \umask($umask);
        }
        $created = \mkdir($directory, $permissions, $recursive);
        $error = ErrorHandler::stop();
        if ($umaskToRestore) {
            \umask($umaskToRestore);
        }
        if (\false === $created && !\is_dir($directory)) {
            throw new RuntimeException(\sprintf('Could not create directory "%s"', $directory), 0, $error);
        }
        ErrorHandler::start();
        if (!\chmod($directory, $permissions)) {
            $oct = \decoct($permissions);
            $error = ErrorHandler::stop();

            throw new RuntimeException("chmod('{$directory}', 0{$oct}) failed", 0, $error);
        }
        ErrorHandler::stop();
    }
}
