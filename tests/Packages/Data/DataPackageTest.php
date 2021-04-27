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

namespace Whoa\Tests\Application\Packages\Data;

use Doctrine\DBAL\Connection;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Whoa\Application\Packages\Data\DataContainerConfigurator;
use Whoa\Application\Packages\Data\DataProvider;
use Whoa\Application\Packages\Data\DataSettings;
use Whoa\Application\Packages\Data\DataSettings as C;
use Whoa\Application\Packages\Data\DoctrineSettings;
use Whoa\Application\Packages\Data\DoctrineSettings as S;
use Whoa\Container\Container;
use Whoa\Contracts\Data\ModelSchemaInfoInterface;
use Whoa\Contracts\Settings\SettingsProviderInterface;
use Whoa\Tests\Application\TestCase;
use Mockery;
use Mockery\Mock;
use Psr\Container\ContainerInterface;
use ReflectionException;

/**
 * @package Whoa\Tests\Application
 */
class DataPackageTest extends TestCase
{
    /**
     * Test provider.
     */
    public function testProvider(): void
    {
        $this->assertNotEmpty(DataProvider::getContainerConfigurators());
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
        $container[SettingsProviderInterface::class] = $provider = Mockery::mock(SettingsProviderInterface::class);
        $appSettings = [];
        $provider->shouldReceive('get')->once()->with(C::class)->andReturn($this->getDataSettings()->get($appSettings));
        $provider->shouldReceive('get')->once()->with(S::class)->andReturn([
            S::KEY_URL => 'sqlite:///',
            S::KEY_MEMORY => true,
            S::KEY_EXEC => [
                'PRAGMA foreign_keys = ON;'
            ],
        ]);

        DataContainerConfigurator::configureContainer($container);

        $this->assertNotNull($container->get(ModelSchemaInfoInterface::class));
        $this->assertNotNull($container->get(Connection::class));
    }

    /**
     * Test settings.
     * @throws ReflectionException
     */
    public function testSettings(): void
    {
        $appSettings = [];
        $this->assertNotEmpty($this->getDataSettings()->get($appSettings));
        $this->assertNotEmpty($this->getDoctrineSettings()->get($appSettings));
    }

    /**
     * @return DataSettings
     */
    private function getDataSettings(): DataSettings
    {
        return new class extends DataSettings {
            /**
             * @inheritdoc
             */
            protected function getSettings(): array
            {
                $modelsFolder = implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', 'Data', 'Models']);
                $migrationsFolder = implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', 'Data', 'Migrations']);
                $seedsFolder = implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', 'Data', 'Seeds']);

                return [
                        static::KEY_MODELS_FOLDER => $modelsFolder,
                        static::KEY_MIGRATIONS_FOLDER => $migrationsFolder,
                        static::KEY_MIGRATIONS_LIST_FILE => $migrationsFolder . DIRECTORY_SEPARATOR . 'migrations.php',
                        static::KEY_SEEDS_FOLDER => $seedsFolder,
                        static::KEY_SEEDS_LIST_FILE => $seedsFolder . DIRECTORY_SEPARATOR . 'seeds.php',
                        static::KEY_SEED_INIT => [DataPackageTest::class, 'initSeeder'],
                    ] + parent::getSettings();
            }
        };
    }

    /**
     * @return DoctrineSettings
     */
    private function getDoctrineSettings(): DoctrineSettings
    {
        return new class extends DoctrineSettings {
            /**
             * @inheritdoc
             */
            protected function getSettings(): array
            {
                $dbFile = implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', 'Data', 'Seeds', 'dummy.sqlite']);

                return [

                        static::KEY_PATH => $dbFile,

                    ] + parent::getSettings();
            }
        };
    }

    /**
     * @param ContainerInterface $container
     * @param string $seedClass
     */
    public static function initSeeder(ContainerInterface $container, string $seedClass)
    {
        assert($container || $seedClass);
    }
}
