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

namespace Whoa\Application\Packages\Session;

use Whoa\Application\Contracts\Session\SessionFunctionsInterface;
use Whoa\Application\Session\Session;
use Whoa\Application\Session\SessionFunctions;
use Whoa\Contracts\Application\ContainerConfiguratorInterface;
use Whoa\Contracts\Container\ContainerInterface as WhoaContainerInterface;
use Whoa\Contracts\Session\SessionInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;

/**
 * @package Whoa\Application
 */
class SessionContainerConfigurator implements ContainerConfiguratorInterface
{
    /** @var callable */
    public const CONFIGURATOR = [self::class, self::CONTAINER_METHOD_NAME];

    /**
     * @inheritdoc
     */
    public static function configureContainer(WhoaContainerInterface $container): void
    {
        $container[SessionInterface::class] = function (PsrContainerInterface $container): SessionInterface {
            /** @var SessionFunctionsInterface $functions */
            $functions = $container->get(SessionFunctionsInterface::class);
            return new Session($functions);
        };

        $container[SessionFunctionsInterface::class] = function (): SessionFunctionsInterface {
            return new SessionFunctions();
        };
    }
}
