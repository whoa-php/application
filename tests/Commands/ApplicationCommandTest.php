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

namespace Whoa\Tests\Application\Commands;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Whoa\Application\Commands\ApplicationCommand;
use Whoa\Application\Exceptions\ConfigurationException;
use Whoa\Container\Container;
use Whoa\Contracts\Application\ApplicationConfigurationInterface;
use Whoa\Contracts\Application\CacheSettingsProviderInterface;
use Whoa\Contracts\Commands\IoInterface;
use Whoa\Contracts\FileSystem\FileSystemInterface;
use Whoa\Contracts\Settings\SettingsProviderInterface;
use Whoa\Tests\Application\TestCase;
use Mockery;
use Mockery\Mock;
use ReflectionException;
use ReflectionMethod;

/**
 * @package Whoa\Tests\Application
 */
class ApplicationCommandTest extends TestCase
{
    /**
     * Test command descriptions.
     */
    public function testCommandDescriptions(): void
    {
        $this->assertNotEmpty(ApplicationCommand::getName());
        $this->assertNotEmpty(ApplicationCommand::getHelp());
        $this->assertNotEmpty(ApplicationCommand::getDescription());
        $this->assertNotEmpty(ApplicationCommand::getArguments());
        $this->assertEmpty(ApplicationCommand::getOptions());
    }

    /**
     * Test command called with invalid action parameter.
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     * @throws ContainerExceptionInterface
     */
    public function testInvalidAction(): void
    {
        $container = new Container();
        $inOut = $this->createInOutMock(
            ApplicationCommand::ARG_ACTION,
            'non_existing_action',
            true
        );

        ApplicationCommand::execute($container, $inOut);

        // Mockery will do checks when the test finishes
        $this->assertTrue(true);
    }

    /**
     * Test action.
     * @throws ReflectionException
     */
    public function testCache(): void
    {
        $container = new Container();

        /** @var Mock $providerMock */
        $providerMock = Mockery::mock(CacheSettingsProviderInterface::class);
        $container[SettingsProviderInterface::class] = $providerMock;
        $container[CacheSettingsProviderInterface::class] = $providerMock;
        $providerMock->shouldReceive('getApplicationConfiguration')->once()->withNoArgs()
            ->andReturn([
                ApplicationConfigurationInterface::KEY_CACHE_FOLDER => '/some/path',
                ApplicationConfigurationInterface::KEY_CACHE_CALLABLE => 'Namespace\\ClassName::methodName',
            ]);
        $providerMock->shouldReceive('serialize')->once()->withNoArgs()->andReturn(['some' => 'cache']);
        /** @var Mock $fsMock */
        $container[FileSystemInterface::class] = $fsMock = Mockery::mock(FileSystemInterface::class);
        $fsMock->shouldReceive('write')->once()->withAnyArgs()->andReturnUndefined();

        $inOut = $this->createInOutMock(ApplicationCommand::ARG_ACTION, ApplicationCommand::ACTION_CREATE_CACHE);

        $method = new ReflectionMethod(ApplicationCommand::class, 'run');
        $method->setAccessible(true);
        $command = new ApplicationCommand();
        $method->invoke($command, $container, $inOut);

        // Mockery will do checks when the test finishes
        $this->assertTrue(true);
    }

    /**
     * Test action.
     * @throws ReflectionException
     */
    public function testCacheInvalidCallable(): void
    {
        $this->expectException(ConfigurationException::class);

        $container = new Container();

        /** @var Mock $providerMock */
        $providerMock = Mockery::mock(CacheSettingsProviderInterface::class);
        $container[SettingsProviderInterface::class] = $providerMock;
        $container[CacheSettingsProviderInterface::class] = $providerMock;
        $providerMock->shouldReceive('getApplicationConfiguration')->once()->withNoArgs()
            ->andReturn([
                ApplicationConfigurationInterface::KEY_CACHE_FOLDER => '/some/path',
                ApplicationConfigurationInterface::KEY_CACHE_CALLABLE => '', // <-- invalid value
            ]);

        $inOut = $this->createInOutMock(ApplicationCommand::ARG_ACTION, ApplicationCommand::ACTION_CREATE_CACHE);

        $method = new ReflectionMethod(ApplicationCommand::class, 'run');
        $method->setAccessible(true);
        $command = new ApplicationCommand();
        $method->invoke($command, $container, $inOut);

        // Mockery will do checks when the test finishes
        $this->assertTrue(true);
    }

    /**
     * Test action.
     * @throws ReflectionException
     */
    public function testClear(): void
    {
        $container = new Container();

        /** @var Mock $providerMock */
        $providerMock = Mockery::mock(CacheSettingsProviderInterface::class);
        $container[SettingsProviderInterface::class] = $providerMock;
        $container[CacheSettingsProviderInterface::class] = $providerMock;
        $providerMock->shouldReceive('getApplicationConfiguration')->once()->withNoArgs()
            ->andReturn([
                // the code requires real existing method
                ApplicationConfigurationInterface::KEY_CACHE_CALLABLE => __METHOD__,
            ]);
        /** @var Mock $fsMock */
        $container[FileSystemInterface::class] = $fsMock = Mockery::mock(FileSystemInterface::class);
        $fsMock->shouldReceive('exists')->once()->withAnyArgs()->andReturn(true);
        $fsMock->shouldReceive('delete')->once()->withAnyArgs()->andReturnUndefined();

        $inOut = $this->createInOutMock(ApplicationCommand::ARG_ACTION, ApplicationCommand::ACTION_CLEAR_CACHE);

        $method = new ReflectionMethod(ApplicationCommand::class, 'run');
        $method->setAccessible(true);
        $command = new ApplicationCommand();
        $method->invoke($command, $container, $inOut);

        // Mockery will do checks when the test finishes
        $this->assertTrue(true);
    }

    /**
     * Test action.
     * @throws ReflectionException
     */
    public function testClearNonExistingCache(): void
    {
        $container = new Container();

        /** @var Mock $providerMock */
        $providerMock = Mockery::mock(CacheSettingsProviderInterface::class);
        $container[SettingsProviderInterface::class] = $providerMock;
        $container[CacheSettingsProviderInterface::class] = $providerMock;
        $providerMock->shouldReceive('getApplicationConfiguration')->once()->withNoArgs()
            ->andReturn([
                ApplicationConfigurationInterface::KEY_CACHE_CALLABLE => 'Namespace\\ClassName::methodName',
            ]);
        /** @var Mock $fsMock */

        $inOut = $this->createInOutMock(ApplicationCommand::ARG_ACTION, ApplicationCommand::ACTION_CLEAR_CACHE);

        $method = new ReflectionMethod(ApplicationCommand::class, 'run');
        $method->setAccessible(true);
        $command = new ApplicationCommand();
        $method->invoke($command, $container, $inOut);

        // Mockery will do checks when the test finishes
        $this->assertTrue(true);
    }

    /**
     * Test action.
     * @throws ReflectionException
     */
    public function testParseCacheCallable1(): void
    {
        $mightBeCallable = '';

        $method = new ReflectionMethod(ApplicationCommand::class, 'parseCacheCallable');
        $method->setAccessible(true);
        $command = new ApplicationCommand();
        $result = $method->invoke($command, $mightBeCallable);

        $this->assertEquals([null, null, null], $result);
    }

    /**
     * Test action.
     * @throws ReflectionException
     */
    public function testParseCacheCallable2(): void
    {
        $mightBeCallable = ['NamespaceName\\ClassName', 'methodName'];

        $method = new ReflectionMethod(ApplicationCommand::class, 'parseCacheCallable');
        $method->setAccessible(true);
        $command = new ApplicationCommand();
        $result = $method->invoke($command, $mightBeCallable);

        $this->assertEquals(['NamespaceName', 'ClassName', 'methodName'], $result);
    }

    /**
     * Test action.
     * @throws ReflectionException
     */
    public function testParseCacheCallable3(): void
    {
        $mightBeCallable = ['NamespaceName\\123ClassName', 'methodName']; // <- numbers not allowed

        $method = new ReflectionMethod(ApplicationCommand::class, 'parseCacheCallable');
        $method->setAccessible(true);
        $command = new ApplicationCommand();
        $result = $method->invoke($command, $mightBeCallable);

        $this->assertEquals([null, null, null], $result);
    }

    /**
     * @param string $argName
     * @param string $argValue
     * @param bool $expectErrors
     * @return IoInterface
     */
    private function createInOutMock(string $argName, string $argValue, bool $expectErrors = false): IoInterface
    {
        /** @var Mock $mock */
        $mock = Mockery::mock(IoInterface::class);
        $mock->shouldReceive('getArgument')->zeroOrMoreTimes()->with($argName)->andReturn($argValue);
        if ($expectErrors === true) {
            $mock->shouldReceive('writeError')->zeroOrMoreTimes()->withAnyArgs()->andReturnSelf();
        }

        $mock->shouldReceive('writeInfo')->zeroOrMoreTimes()->withAnyArgs()->andReturnSelf();

        /** @var IoInterface $mock */

        return $mock;
    }
}
