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

namespace Whoa\Tests\Application\Packages\PDO;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Whoa\Application\Packages\PDO\PdoContainerConfigurator;
use Whoa\Application\Packages\PDO\PdoProvider;
use Whoa\Application\Packages\PDO\PdoSettings;
use Whoa\Application\Packages\PDO\PdoSettings as C;
use Whoa\Container\Container;
use Whoa\Contracts\Settings\Packages\PdoSettingsInterface;
use Whoa\Contracts\Settings\SettingsProviderInterface;
use Whoa\Tests\Application\TestCase;
use Mockery;
use Mockery\Mock;
use PDO;

/**
 * @package Whoa\Tests\Application
 */
class PdoPackageTest extends TestCase
{
    /**
     * Test provider.
     */
    public function testProvider(): void
    {
        $this->assertNotEmpty(PdoProvider::getContainerConfigurators());
    }

    /**
     * Test settings.
     */
    public function testSettings(): void
    {
        $appSettings = [];
        $this->assertNotEmpty($this->createSettings()->get($appSettings));
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
        $container[SettingsProviderInterface::class] = $provider = Mockery::mock(SettingsProviderInterface::class);
        $provider->shouldReceive('get')->once()->with(C::class)->andReturn([
            PdoSettingsInterface::KEY_CONNECTION_STRING => 'sqlite::memory:',
        ]);

        PdoContainerConfigurator::configureContainer($container);

        $this->assertNotNull($container->get(PDO::class));
    }

    /**
     * @return PdoSettings
     */
    private function createSettings(): PdoSettings
    {
        return new class extends PdoSettings {
            /**
             * @inheritdoc
             */
            protected function getSettings(): array
            {
                return [
                        self::KEY_CONNECTION_STRING => 'some connection string',
                    ] + parent::getSettings();
            }
        };
    }
}
