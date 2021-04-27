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

namespace Whoa\Tests\Application\Packages\Cors;

use Whoa\Application\Packages\Cors\CorsContainerConfigurator;
use Whoa\Application\Packages\Cors\CorsProvider;
use Whoa\Application\Packages\Cors\CorsSettings as C;
use Whoa\Container\Container;
use Whoa\Contracts\Application\ApplicationConfigurationInterface as A;
use Whoa\Contracts\Settings\SettingsProviderInterface;
use Whoa\Tests\Application\TestCase;
use Mockery;
use Mockery\Mock;
use Neomerx\Cors\Contracts\AnalyzerInterface;
use Neomerx\Cors\Strategies\Settings;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @package Whoa\Tests\Application
 */
class CorsPackageTest extends TestCase
{
    /**
     * Test provider.
     */
    public function testProvider(): void
    {
        $this->assertNotEmpty(CorsProvider::getSettings());
        $this->assertNotEmpty(CorsProvider::getMiddleware());
        $this->assertNotEmpty(CorsProvider::getContainerConfigurators());
    }

    /**
     * Test container configurator.
     */
    public function testContainerConfigurator(): void
    {
        $container = new Container();

        $appConfig = [
            A::KEY_IS_DEBUG => true,
            A::KEY_IS_LOG_ENABLED => true,
            A::KEY_APP_ORIGIN_SCHEMA => 'http',
            A::KEY_APP_ORIGIN_HOST => 'localhost',
            A::KEY_APP_ORIGIN_PORT => '8080',
        ];

        /** @var Mock $provider */
        $provider = Mockery::mock(SettingsProviderInterface::class);
        $container[SettingsProviderInterface::class] = $provider;
        $container[LoggerInterface::class] = new NullLogger();

        $corsConfig = (new C())->get($appConfig);
        $provider->shouldReceive('get')->once()->with(C::class)->andReturn($corsConfig);

        CorsContainerConfigurator::configureContainer($container);

        $this->assertNotNull($container->get(AnalyzerInterface::class));
    }

    /**
     * Test package settings.
     */
    public function testPackageSettings(): void
    {
        $appSettings = [
            A::KEY_APP_ORIGIN_SCHEMA => 'http',
            A::KEY_APP_ORIGIN_HOST => 'localhost',
            A::KEY_APP_ORIGIN_PORT => 80,
        ];

        // add some test coverage

        // emulate custom settings on application level
        $customSettings = new class extends C {
            /**
             * @inheritdoc
             */
            protected function getSettings(): array
            {
                return [
                        static::KEY_IS_FORCE_ADD_METHODS => true,
                        static::KEY_IS_FORCE_ADD_HEADERS => true,
                    ] + parent::getSettings();
            }
        };

        // emulate caching settings
        $packageSettings = $customSettings->get($appSettings);

        // now emulate restore settings from cache
        [$corsCachedData,] = $packageSettings;
        $corsSettings = (new Settings())->setData($corsCachedData);

        // now check that settings for adding methods and headers were set
        $this->assertTrue($corsSettings->isForceAddAllowedMethodsToPreFlightResponse());
        $this->assertTrue($corsSettings->isForceAddAllowedHeadersToPreFlightResponse());
    }
}
