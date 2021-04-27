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

namespace Whoa\Tests\Application\Packages\Monolog;

use Whoa\Application\Packages\Monolog\MonologFileContainerConfigurator;
use Whoa\Application\Packages\Monolog\MonologFileProvider;
use Whoa\Application\Packages\Monolog\MonologFileSettings as C;
use Whoa\Container\Container;
use Whoa\Contracts\Application\ApplicationConfigurationInterface as A;
use Whoa\Contracts\Application\CacheSettingsProviderInterface;
use Whoa\Contracts\Settings\Packages\MonologFileSettingsInterface;
use Whoa\Tests\Application\TestCase;
use Mockery;
use Mockery\Mock;
use Psr\Log\LoggerInterface;

/**
 * @package Whoa\Tests\Application
 */
class MonologPackageTest extends TestCase
{
    /**
     * Test provider.
     */
    public function testProvider(): void
    {
        $this->assertNotEmpty(MonologFileProvider::getContainerConfigurators());
    }

    /**
     * Test container configurator.
     */
    public function testContainerConfigurator(): void
    {
        $container = new Container();

        /** @var Mock $provider */
        $provider = Mockery::mock(CacheSettingsProviderInterface::class);
        $container[CacheSettingsProviderInterface::class] = $provider;
        $provider->shouldReceive('getApplicationConfiguration')->once()->withNoArgs()->andReturn([
            A::KEY_APP_NAME => 'Test_App',
        ]);
        $provider->shouldReceive('get')->once()->with(C::class)->andReturn([
            MonologFileSettingsInterface::KEY_IS_ENABLED => true,
            MonologFileSettingsInterface::KEY_LOG_PATH => '/some/path',
        ]);

        MonologFileContainerConfigurator::configureContainer($container);

        $this->assertNotNull($container->get(LoggerInterface::class));
    }

    /**
     * Test settings.
     */
    public function testSettings(): void
    {
        $appSettings = [];
        $this->assertNotEmpty($this->getSettings()->get($appSettings));
    }

    /**
     * @return C
     */
    private function getSettings(): C
    {
        return new class extends C {
            /**
             * @inheritdoc
             */
            protected function getSettings(): array
            {
                return [
                        MonologFileSettingsInterface::KEY_LOG_FOLDER => __DIR__,
                    ] + parent::getSettings();
            }
        };
    }
}
