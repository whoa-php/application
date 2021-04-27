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

namespace Whoa\Application\Packages\Monolog;

use Exception;
use Whoa\Application\Packages\Monolog\MonologFileSettings as C;
use Whoa\Contracts\Application\ApplicationConfigurationInterface as A;
use Whoa\Contracts\Application\CacheSettingsProviderInterface;
use Whoa\Contracts\Application\ContainerConfiguratorInterface;
use Whoa\Contracts\Container\ContainerInterface as WhoaContainerInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Monolog\Processor\WebProcessor;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Log\LoggerInterface;
use Whoa\Contracts\Settings\Packages\MonologFileSettingsInterface;

use function array_key_exists;
use function assert;

/**
 * @package Whoa\Application
 */
class MonologFileContainerConfigurator implements ContainerConfiguratorInterface
{
    /** @var callable */
    public const CONFIGURATOR = [self::class, self::CONTAINER_METHOD_NAME];

    /**
     * @inheritdoc
     */
    public static function configureContainer(WhoaContainerInterface $container): void
    {
        $container[LoggerInterface::class] = function (PsrContainerInterface $container) {
            /** @var CacheSettingsProviderInterface $settingsProvider */
            $settingsProvider = $container->get(CacheSettingsProviderInterface::class);
            $appConfig = $settingsProvider->getApplicationConfiguration();
            $monologSettings = $settingsProvider->get(C::class);

            $monolog = new Logger($appConfig[A::KEY_APP_NAME]);
            $handler = $monologSettings[MonologFileSettingsInterface::KEY_IS_ENABLED] === true ?
                static::createHandler($monologSettings) : new NullHandler();

            $monolog->pushHandler($handler);

            return $monolog;
        };
    }

    /**
     * @param array $settings
     * @return HandlerInterface
     * @throws Exception
     */
    protected static function createHandler(array $settings): HandlerInterface
    {
        assert(array_key_exists(MonologFileSettingsInterface::KEY_LOG_PATH, $settings) === true);

        $logPath = $settings[MonologFileSettingsInterface::KEY_LOG_PATH];
        $logLevel = $settings[MonologFileSettingsInterface::KEY_LOG_LEVEL] ?? Logger::ERROR;
        $handler = new StreamHandler($logPath, $logLevel);
        $handler->setFormatter(new LineFormatter(null, null, true, true));
        $handler->pushProcessor(new WebProcessor());
        $handler->pushProcessor(new UidProcessor());

        return $handler;
    }
}
