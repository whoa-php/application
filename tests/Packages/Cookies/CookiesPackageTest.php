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

namespace Whoa\Tests\Application\Packages\Cookies;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Whoa\Application\Contracts\Cookie\CookieFunctionsInterface;
use Whoa\Application\Packages\Cookies\CookieContainerConfigurator;
use Whoa\Application\Packages\Cookies\CookieProvider;
use Whoa\Application\Packages\Cookies\CookieSettings as C;
use Whoa\Container\Container;
use Whoa\Contracts\Cookies\CookieJarInterface;
use Whoa\Contracts\Settings\SettingsProviderInterface;
use Whoa\Tests\Application\TestCase;
use Mockery;
use Mockery\Mock;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @package Whoa\Tests\Application
 */
class CookiesPackageTest extends TestCase
{
    /**
     * Test provider.
     */
    public function testProvider(): void
    {
        $this->assertNotEmpty(CookieProvider::getSettings());
        $this->assertNotEmpty(CookieProvider::getMiddleware());
        $this->assertNotEmpty(CookieProvider::getContainerConfigurators());
    }

    /**
     * Test container configurator.
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function testContainerConfigurator(): void
    {
        $container = new Container();

        /** @var Mock $provider */
        $provider = Mockery::mock(SettingsProviderInterface::class);
        $container[SettingsProviderInterface::class] = $provider;
        $container[LoggerInterface::class] = new NullLogger();
        $appSettings = [];
        $corsConfig = (new C())->get($appSettings);
        $provider->shouldReceive('get')->once()->with(C::class)->andReturn($corsConfig);

        CookieContainerConfigurator::configureContainer($container);

        $this->assertNotNull($container->get(CookieJarInterface::class));
        $this->assertNotNull($container->get(CookieFunctionsInterface::class));
    }
}
