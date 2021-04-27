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

namespace Whoa\Tests\Application\Packages\Csrf;

use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\ServerRequest;
use Whoa\Application\Contracts\Csrf\CsrfTokenStorageInterface;
use Whoa\Application\Packages\Csrf\CsrfMiddleware;
use Whoa\Application\Packages\Csrf\CsrfSettings as C;
use Whoa\Container\Container;
use Whoa\Contracts\Settings\SettingsProviderInterface;
use Whoa\Tests\Application\TestCase;
use Mockery;
use Mockery\Mock;
use Psr\Http\Message\ResponseInterface;
use ReflectionException;

/**
 * @package Whoa\Tests\Application
 */
class CsrfMiddlewareTest extends TestCase
{
    /** @var Container */
    private Container $container;

    /**
     * @inheritdoc
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $container = new Container();

        /** @var Mock $provider */
        $provider = Mockery::mock(SettingsProviderInterface::class);
        $container[SettingsProviderInterface::class] = $provider;

        $provider->shouldReceive('has')->zeroOrMoreTimes()->with(C::class)->andReturn(true);
        $provider->shouldReceive('get')->once()->with(C::class)->andReturn($this->getDefaultCsrfSettings());

        $this->container = $container;
    }

    /**
     * Test container configurator.
     */
    public function testHandlerWithValidRequest(): void
    {
        $parsedBody = [C::DEFAULT_HTTP_REQUEST_CSRF_TOKEN_KEY => 'whatever'];
        $request = new ServerRequest([], [], null, 'POST', 'php://input', [], [], [], $parsedBody);
        $next = function (): ResponseInterface {
            return new EmptyResponse();
        };

        $storageMock = Mockery::mock(CsrfTokenStorageInterface::class);
        $storageMock->shouldReceive('check')->once()->withAnyArgs()->andReturn(true);
        $this->container[CsrfTokenStorageInterface::class] = $storageMock;

        $response = CsrfMiddleware::handle($request, $next, $this->container);
        $this->assertEquals(204, $response->getStatusCode());
    }

    /**
     * Test container configurator.
     */
    public function testHandlerWithInvalidRequest(): void
    {
        $parsedBody = [C::DEFAULT_HTTP_REQUEST_CSRF_TOKEN_KEY => 'whatever'];
        $request = new ServerRequest([], [], null, 'POST', 'php://input', [], [], [], $parsedBody);
        $next = function (): ResponseInterface {
            return new EmptyResponse();
        };

        $storageMock = Mockery::mock(CsrfTokenStorageInterface::class);
        $storageMock->shouldReceive('check')->once()->withAnyArgs()->andReturn(false);
        $this->container[CsrfTokenStorageInterface::class] = $storageMock;

        $response = CsrfMiddleware::handle($request, $next, $this->container);
        $this->assertEquals(403, $response->getStatusCode());
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    private function getDefaultCsrfSettings(): array
    {
        $appConfig = [];
        return (new C())->get($appConfig);
    }
}
