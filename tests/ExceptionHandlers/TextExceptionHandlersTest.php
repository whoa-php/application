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

namespace Whoa\Tests\Application\ExceptionHandlers;

use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Whoa\Application\ExceptionHandlers\WhoopsThrowableJsonHandler;
use Whoa\Application\ExceptionHandlers\WhoopsThrowableTextHandler;
use Whoa\Container\Container;
use Whoa\Contracts\Application\ApplicationConfigurationInterface as A;
use Whoa\Contracts\Application\CacheSettingsProviderInterface;
use Whoa\Contracts\Http\ThrowableResponseInterface;
use Whoa\Tests\Application\TestCase;
use Mockery;
use Mockery\Mock;
use Psr\Container\ContainerInterface;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @package Whoa\Tests\Application
 */
class TextExceptionHandlersTest extends TestCase
{
    /**
     * Test handler.
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function testDefaultExceptionHandler(): void
    {
        $handler = new WhoopsThrowableTextHandler();

        $response = $handler->createResponse(new Exception('Error for Text handler'), $this->createContainer(true));
        $this->assertInstanceOf(ThrowableResponseInterface::class, $response);
        $this->assertEquals(['text/plain; charset=utf-8'], $response->getHeader('Content-Type'));
        $this->assertStringStartsWith('Exception: Error for Text handler in file', (string)$response->getBody());
    }

    /**
     * Test handler.
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function testDefaultExceptionHandlerDebugDisabled(): void
    {
        $handler = new WhoopsThrowableTextHandler();

        $response = $handler->createResponse(new Exception('Error for Text handler'), $this->createContainer(false));
        $this->assertInstanceOf(ThrowableResponseInterface::class, $response);
        $this->assertEquals(['text/plain; charset=utf-8'], $response->getHeader('Content-Type'));
        $this->assertEquals('Internal Server Error', (string)$response->getBody());
    }

    /**
     * Test handler.
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function testFaultyLoggerOnError(): void
    {
        $handler = new WhoopsThrowableTextHandler();

        $response = $handler->createResponse(new Exception(), $this->createContainerWithFaultyLogger());
        $this->assertInstanceOf(ThrowableResponseInterface::class, $response);
        $this->assertEquals(['text/plain; charset=utf-8'], $response->getHeader('Content-Type'));
        $this->assertStringStartsWith('Exception:  in file', (string)$response->getBody());
    }

    /**
     * Test handler.
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function testDefaultJsonExceptionHandler(): void
    {
        $handler = new WhoopsThrowableJsonHandler();

        $response = $handler->createResponse(new Exception('Error for JSON handler'), $this->createContainer(true));
        $this->assertInstanceOf(ThrowableResponseInterface::class, $response);
        $this->assertEquals(['application/json'], $response->getHeader('Content-Type'));
        $this->assertStringStartsWith(
            '"{\"error\":{\"type\":\"Exception\",\"message\":\"Error for JSON handler\"',
            json_encode(json_decode((string)$response->getBody()))
        );
    }

    /**
     * @param bool $debugEnabled
     * @return ContainerInterface
     */
    private function createContainer(bool $debugEnabled): ContainerInterface
    {
        $container = new Container();

        $container[LoggerInterface::class] = new NullLogger();

        /** @var Mock $provider */
        $provider = Mockery::mock(CacheSettingsProviderInterface::class);
        $container[CacheSettingsProviderInterface::class] = $provider;
        $provider->shouldReceive('getApplicationConfiguration')->once()->withNoArgs()->andReturn([
            A::KEY_IS_DEBUG => $debugEnabled,
            A::KEY_APP_NAME => 'Test App',
            A::KEY_EXCEPTION_DUMPER => [self::class, 'exceptionDumper'],
        ]);

        return $container;
    }

    /**
     * @return ContainerInterface
     */
    private function createContainerWithFaultyLogger(): ContainerInterface
    {
        /** @var Container $container */
        $container = $this->createContainer(true);

        $container[LoggerInterface::class] = new class extends AbstractLogger implements LoggerInterface {
            /**
             * @inheritdoc
             */
            public function log($level, $message, array $context = [])
            {
                // emulate such error as no permission to write logs to disk.
                throw new Exception();
            }
        };

        return $container;
    }

    /**
     * @param array ...$args
     * @return array
     */
    public static function exceptionDumper(...$args): array
    {
        assert($args);

        return [
            'some' => 'related details',
        ];
    }
}
