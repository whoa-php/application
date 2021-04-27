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

namespace Whoa\Tests\Application\Packages\Authorization;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Whoa\Application\Packages\Authorization\AuthorizationContainerConfigurator;
use Whoa\Application\Packages\Authorization\AuthorizationProvider;
use Whoa\Application\Packages\Authorization\AuthorizationSettings as C;
use Whoa\Container\Container;
use Whoa\Contracts\Authorization\AuthorizationManagerInterface;
use Whoa\Contracts\Settings\SettingsProviderInterface;
use Whoa\Tests\Application\TestCase;
use Mockery;
use Mockery\Mock;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionException;

/**
 * @package Whoa\Tests\Application
 */
class AuthorizationPackageTest extends TestCase
{
    /**
     * Test provider.
     */
    public function testProvider(): void
    {
        $this->assertNotEmpty(AuthorizationProvider::getContainerConfigurators());
    }

    /**
     * Test container configurator.
     * @throws ReflectionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testContainerConfigurator(): void
    {
        $container = new Container();

        /** @var Mock $provider */
        $provider = Mockery::mock(SettingsProviderInterface::class);
        $container[SettingsProviderInterface::class] = $provider;
        $container[LoggerInterface::class] = new NullLogger();
        $appSettings = [];
        $provider->shouldReceive('get')->once()->with(C::class)
            ->andReturn($this->getAuthorizationSettings()->get($appSettings));

        AuthorizationContainerConfigurator::configureContainer($container);

        $this->assertNotNull($container->get(AuthorizationManagerInterface::class));
    }

    /**
     * @return C
     */
    public static function getAuthorizationSettings(): C
    {
        return new class extends C {
            /**
             * @inheritdoc
             */
            protected function getSettings(): array
            {
                $policiesFolder = implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', 'Data', 'Authorization']);

                return [
                        static::KEY_POLICIES_FOLDER => $policiesFolder,
                    ] + parent::getSettings();
            }
        };
    }
}
