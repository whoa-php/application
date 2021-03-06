<?php

/*
 * Copyright 2015-2020 info@neomerx.com
 * Modification Copyright 2021-2022 info@whoaphp.com
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace Whoa\Application\FileSystem;

use DirectoryIterator;
use Whoa\Application\Exceptions\FileSystemException;
use Whoa\Contracts\FileSystem\FileSystemInterface;

use function call_user_func;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function is_writable;
use function iterator_to_array;
use function mkdir;
use function rmdir;
use function symlink;
use function unlink;

/**
 * @package Whoa\Application
 */
class FileSystem implements FileSystemInterface
{
    /**
     * @inheritdoc
     */
    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    /**
     * @inheritdoc
     */
    public function read(string $filePath): string
    {
        $content = file_get_contents($filePath);
        $content !== false ?: $this->throwEx(new FileSystemException());

        return $content;
    }

    /**
     * @inheritdoc
     */
    public function write(string $filePath, string $contents): void
    {
        $bytesWritten = file_put_contents($filePath, $contents);
        $bytesWritten !== false ?: $this->throwEx(new FileSystemException());
    }

    /**
     * @inheritdoc
     */
    public function delete(string $filePath): void
    {
        $isDeleted = file_exists($filePath) === false || unlink($filePath) === true;
        $isDeleted === true ?: $this->throwEx(new FileSystemException());
    }

    /**
     * @inheritdoc
     */
    public function scanFolder(string $folderPath): array
    {
        is_dir($folderPath) === true ?: $this->throwEx(new FileSystemException());

        $iterator = call_user_func(function () use ($folderPath) {
            foreach (new DirectoryIterator($folderPath) as $directoryIterator) {
                /** @var DirectoryIterator $directoryIterator */
                if ($directoryIterator->isDot() === false) {
                    yield $directoryIterator->getFilename() => $directoryIterator->getRealPath();
                }
            }
        });

        return iterator_to_array($iterator);
    }

    /**
     * @inheritdoc
     */
    public function isFolder(string $path): bool
    {
        return is_dir($path);
    }

    /**
     * @inheritdoc
     */
    public function isWritable(string $path): bool
    {
        return is_writable($path);
    }

    /**
     * @inheritdoc
     */
    public function createFolder(string $folderPath): void
    {
        $isCreated = mkdir($folderPath);
        $isCreated === true ?: $this->throwEx(new FileSystemException());
    }

    /**
     * @inheritdoc
     */
    public function deleteFolder(string $folderPath): void
    {
        $isDeleted = is_dir($folderPath) === true && rmdir($folderPath) === true;
        $isDeleted === true ?: $this->throwEx(new FileSystemException());
    }

    /**
     * @inheritdoc
     */
    public function deleteFolderRecursive(string $folderPath): void
    {
        foreach ($this->scanFolder($folderPath) as $path) {
            $this->isFolder($path) === true ? $this->deleteFolderRecursive($path) : $this->delete($path);
        }

        $this->deleteFolder($folderPath);
    }

    /**
     * @inheritdoc
     */
    public function symlink(string $targetPath, string $linkPath): void
    {
        $isCreated = symlink($targetPath, $linkPath) === true;
        $isCreated === true ?: $this->throwEx(new FileSystemException());
    }

    /**
     * @inheritdoc
     */
    public function requireFile(string $path)
    {
        return require $path;
    }

    /**
     * @param FileSystemException $exception
     * @return void
     */
    protected function throwEx(FileSystemException $exception): void
    {
        throw $exception;
    }
}
