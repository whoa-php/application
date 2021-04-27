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

namespace Whoa\Tests\Application\Data\CoreSettings\Providers;

use Laminas\Diactoros\Response\EmptyResponse;
use Whoa\Application\Commands\DataCommand;
use Whoa\Contracts\Application\ContainerConfiguratorInterface as CCI;
use Whoa\Contracts\Application\RoutesConfiguratorInterface as RCI;
use Whoa\Contracts\Container\ContainerInterface as WhoaContainerInterface;
use Whoa\Contracts\Provider\ProvidesCommandsInterface as PrCmdI;
use Whoa\Contracts\Provider\ProvidesContainerConfiguratorsInterface as PrCCI;
use Whoa\Contracts\Provider\ProvidesMessageResourcesInterface as MRI;
use Whoa\Contracts\Provider\ProvidesMiddlewareInterface as PrMI;
use Whoa\Contracts\Provider\ProvidesRouteConfiguratorsInterface as PrRCI;
use Whoa\Contracts\Provider\ProvidesSettingsInterface as PrSI;
use Whoa\Contracts\Routing\GroupInterface;
use Whoa\Contracts\Settings\SettingsInterface;
use Whoa\Tests\Application\Data\CoreSettings\Middleware\PluginMiddleware;
use Whoa\Tests\Application\Data\L10n\SampleEnUsMessages;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @package Whoa\Tests\Application
 */
class Provider1 implements PrCCI, PrMI, PrRCI, PrSI, CCI, RCI, PrCmdI, MRI
{
    /**
     * @inheritdoc
     */
    public static function getContainerConfigurators(): array
    {
        return [
            static::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getMiddleware(): array
    {
        return [
            PluginMiddleware::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getRouteConfigurators(): array
    {
        return [
            static::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getCommands(): array
    {
        return [
            DataCommand::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getSettings(): array
    {
        return [
            new class implements SettingsInterface {
                /**
                 * @inheritdoc
                 */
                public function get(array $appConfig): array
                {
                    return ['Provider1_Settings' => 'some value'];
                }
            }
        ];
    }

    /**
     * @inheritdoc
     */
    public static function configureContainer(WhoaContainerInterface $container): void
    {
        $container[static::class] = 'Hello container';
    }

    /**
     * @inheritdoc
     */
    public static function configureRoutes(GroupInterface $routes): void
    {
        $routes->get('/plugin1', [static::class, 'onIndex']);
    }

    /**
     * @param array $parameters
     * @param PsrContainerInterface $container
     * @param ServerRequestInterface|null $request
     * @return ResponseInterface
     */
    public static function onIndex(
        array $parameters,
        PsrContainerInterface $container,
        ServerRequestInterface $request = null
    ): ResponseInterface {
        assert(($parameters && $container && $request) || true);

        return new EmptyResponse();
    }

    /**
     * @inheritdoc
     */
    public static function getMessageDescriptions(): array
    {
        return [
            ['en_US', 'sample.messages.namespace', SampleEnUsMessages::class],
        ];
    }
}
