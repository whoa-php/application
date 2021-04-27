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

namespace Whoa\Tests\Application\Packages\Session;

use Closure;
use Whoa\Application\Contracts\Session\SessionFunctionsInterface;
use Whoa\Application\Packages\Session\SessionMiddleware;
use Whoa\Application\Packages\Session\SessionSettings;
use Whoa\Application\Session\SessionFunctions;
use Whoa\Container\Container;
use Whoa\Contracts\Settings\SettingsProviderInterface;
use Whoa\Tests\Application\TestCase;
use Mockery;
use Mockery\Mock;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @package Whoa\Tests\Application
 */
class SessionMiddlewareTest extends TestCase
{
    /**
     * @var ServerRequestInterface
     */
    private $request;

    /**
     * @var Closure
     */
    private Closure $next;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->request = Mockery::mock(ServerRequestInterface::class);
        $responseMock = Mockery::mock(ResponseInterface::class);
        $this->next = function () use ($responseMock) {
            return $responseMock;
        };
    }

    /**
     * Test setting cookies.
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testSettingCookies(): void
    {
        $sessionStartCalled = false;
        $sessionCloseCalled = false;

        $settingsFunctions = new SessionFunctions();
        $settingsFunctions->setStartCallable(function () use (&$sessionStartCalled) {
            $sessionStartCalled = true;
        });
        $settingsFunctions->setWriteCloseCallable(function () use (&$sessionCloseCalled) {
            $sessionCloseCalled = true;
        });

        /** @var Mock $providerMock */
        $providerMock = Mockery::mock(SettingsProviderInterface::class);
        $appSettings = [];
        $providerMock->shouldReceive('get')->once()->with(SessionSettings::class)
            ->andReturn((new SessionSettings())->get($appSettings));

        $container = new Container();
        $container[SessionFunctionsInterface::class] = $settingsFunctions;
        $container[SettingsProviderInterface::class] = $providerMock;

        SessionMiddleware::handle($this->request, $this->next, $container);

        $this->assertTrue($sessionStartCalled);
        $this->assertTrue($sessionCloseCalled);
    }
}
