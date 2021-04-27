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

namespace Whoa\Tests\Application\Packages\FileSystem;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Whoa\Application\Packages\FileSystem\FileSystemContainerConfigurator;
use Whoa\Application\Packages\FileSystem\FileSystemProvider;
use Whoa\Container\Container;
use Whoa\Contracts\FileSystem\FileSystemInterface;
use Whoa\Tests\Application\TestCase;

/**
 * @package Whoa\Tests\Application
 */
class FileSystemPackageTest extends TestCase
{
    /**
     * Test provider.
     */
    public function testProvider(): void
    {
        $this->assertNotEmpty(FileSystemProvider::getContainerConfigurators());
    }

    /**
     * Test container configurator.
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function testContainerConfigurator(): void
    {
        $container = new Container();

        FileSystemContainerConfigurator::configureContainer($container);

        $this->assertNotNull($container->get(FileSystemInterface::class));
    }
}
