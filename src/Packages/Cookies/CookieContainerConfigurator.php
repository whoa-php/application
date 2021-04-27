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

namespace Whoa\Application\Packages\Cookies;

use Whoa\Application\Contracts\Cookie\CookieFunctionsInterface;
use Whoa\Application\Cookies\CookieFunctions;
use Whoa\Application\Cookies\CookieJar;
use Whoa\Contracts\Application\ContainerConfiguratorInterface;
use Whoa\Contracts\Container\ContainerInterface as WhoaContainerInterface;
use Whoa\Contracts\Cookies\CookieJarInterface;
use Whoa\Contracts\Settings\Packages\CookieSettingsInterface;
use Whoa\Contracts\Settings\SettingsProviderInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;

/**
 * @package Whoa\Application
 */
class CookieContainerConfigurator implements ContainerConfiguratorInterface
{
    /** @var callable */
    public const CONFIGURATOR = [self::class, self::CONTAINER_METHOD_NAME];

    /**
     * @inheritdoc
     */
    public static function configureContainer(WhoaContainerInterface $container): void
    {
        $container[CookieJarInterface::class] =
            function (PsrContainerInterface $container): CookieJarInterface {
                $settings = $container->get(SettingsProviderInterface::class)->get(CookieSettings::class);

                return new CookieJar(
                    $settings[CookieSettingsInterface::KEY_DEFAULT_PATH],
                    $settings[CookieSettingsInterface::KEY_DEFAULT_DOMAIN],
                    $settings[CookieSettingsInterface::KEY_DEFAULT_IS_SEND_ONLY_OVER_SECURE_CONNECTION],
                    $settings[CookieSettingsInterface::KEY_DEFAULT_IS_ACCESSIBLE_ONLY_THROUGH_HTTP],
                    $settings[CookieSettingsInterface::KEY_DEFAULT_IS_RAW]
                );
            };

        $container[CookieFunctionsInterface::class] =
            function (PsrContainerInterface $container): CookieFunctionsInterface {
                return new CookieFunctions();
            };
    }
}
