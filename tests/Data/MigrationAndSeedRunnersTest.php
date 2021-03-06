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

namespace Whoa\Tests\Application\Data;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Whoa\Application\Data\BaseMigrationRunner;
use Whoa\Application\Data\FileMigrationRunner;
use Whoa\Application\Data\FileSeedRunner;
use Whoa\Application\FileSystem\FileSystem;
use Whoa\Container\Container;
use Whoa\Contracts\Commands\IoInterface;
use Whoa\Contracts\FileSystem\FileSystemInterface;
use Whoa\Tests\Application\TestCase;
use Mockery;
use Mockery\Mock;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use ReflectionMethod;

/**
 * @package Whoa\Tests\Application
 */
class MigrationAndSeedRunnersTest extends TestCase
{
    /** Path to migrations list */
    public const MIGRATIONS_PATH =
        __DIR__ . DIRECTORY_SEPARATOR . 'Migrations' . DIRECTORY_SEPARATOR . 'migrations.php';

    /** Path to seeds list */
    public const SEEDS_PATH =
        __DIR__ . DIRECTORY_SEPARATOR . 'Seeds' . DIRECTORY_SEPARATOR . 'seeds.php';

    /**
     * Test migrations and Seeds.
     * @throws DBALException
     * @throws InvalidArgumentException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testMigrationsAndSeeds(): void
    {
        /** @var Mock $inOut */
        $inOut = Mockery::mock(IoInterface::class);
        $inOut->shouldReceive('writeInfo')->zeroOrMoreTimes()->withAnyArgs()->andReturnSelf();

        /** @var IoInterface $inOut */

        $migrationRunner = new FileMigrationRunner($inOut, static::MIGRATIONS_PATH);
        $seedRunner = new FileSeedRunner($inOut, static::SEEDS_PATH, [static::class, 'seedInit']);

        $container = $this->createContainer();
        /** @var Connection $connection */
        $connection = $container->get(Connection::class);
        $manager = $connection->getSchemaManager();

        $migrationRunner->migrate($container);

        $this->assertTrue($manager->tablesExist([BaseMigrationRunner::MIGRATIONS_TABLE]));
        $this->assertFalse($manager->tablesExist([BaseMigrationRunner::SEEDS_TABLE]));

        // check second run causes no problem
        $migrationRunner->migrate($container);

        $seedRunner->run($container);

        $this->assertTrue($manager->tablesExist([BaseMigrationRunner::SEEDS_TABLE]));

        // check second run causes no problem
        $seedRunner->run($container);

        $migrationRunner->rollback($container);

        $this->assertFalse($manager->tablesExist([BaseMigrationRunner::MIGRATIONS_TABLE]));
        $this->assertFalse($manager->tablesExist([BaseMigrationRunner::SEEDS_TABLE]));
    }

    /**
     * Test migration. Sometimes migration are renamed and rollback fails as it can't find the original class.
     * It needs to handle such situations.
     * @throws DBALException
     * @throws ReflectionException
     */
    public function testInvalidMigrationClass(): void
    {
        // This test is designed to test/cover specific implementation rather than functionality and
        // needed to simulate very specific situation.

        /** @var Mock $inOut */
        $inOut = Mockery::mock(IoInterface::class);
        $inOut->shouldReceive('writeWarning')->once()->withAnyArgs()->andReturnSelf();

        /** @var IoInterface $inOut */

        $runner = Mockery::mock(BaseMigrationRunner::class);
        $runner->makePartial();

        $container = $this->createContainer();

        $method = new ReflectionMethod(BaseMigrationRunner::class, 'setIO');
        $method->setAccessible(true);
        $method->invoke($runner, $inOut);

        $method = new ReflectionMethod(BaseMigrationRunner::class, 'createMigration');
        $method->setAccessible(true);
        $nullMigration = $method->invoke($runner, 'non-existing-class', $container);

        $this->assertNull($nullMigration);
    }

    /**
     * @param ContainerInterface $container
     * @param string $seederClass
     * @return void
     */
    public static function seedInit(ContainerInterface $container, string $seederClass): void
    {
        assert($container && $seederClass);
    }

    /**
     * @return ContainerInterface
     * @throws DBALException
     */
    private function createContainer(): ContainerInterface
    {
        $container = new Container();

        $container[FileSystemInterface::class] = new FileSystem();
        $container[Connection::class] = $this->createConnection();

        return $container;
    }

    /**
     * @return Connection
     * @throws DBALException
     */
    private function createConnection(): Connection
    {
        // user and password are needed for HHVM
        $connection = DriverManager::getConnection([
            'url' => 'sqlite:///',
            'memory' => true,
            'dbname' => 'test',
            'user' => '',
            'password' => '',
        ]);
        $this->assertNotSame(false, $connection->exec('PRAGMA foreign_keys = ON;'));

        return $connection;
    }
}
