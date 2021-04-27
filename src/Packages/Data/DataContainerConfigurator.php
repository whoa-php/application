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

namespace Whoa\Application\Packages\Data;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Whoa\Application\Data\ModelSchemaInfo;
use Whoa\Contracts\Application\ContainerConfiguratorInterface;
use Whoa\Contracts\Container\ContainerInterface as WhoaContainerInterface;
use Whoa\Contracts\Data\ModelSchemaInfoInterface;
use Whoa\Contracts\Settings\Packages\DataSettingsInterface;
use Whoa\Contracts\Settings\Packages\DoctrineSettingsInterface;
use Whoa\Contracts\Settings\SettingsProviderInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;

use function array_filter;
use function array_key_exists;
use function is_array;

/**
 * @package Whoa\Application
 */
class DataContainerConfigurator implements ContainerConfiguratorInterface
{
    /** @var callable */
    public const CONFIGURATOR = [self::class, self::CONTAINER_METHOD_NAME];

    /**
     * @inheritdoc
     */
    public static function configureContainer(WhoaContainerInterface $container): void
    {
        $container[ModelSchemaInfoInterface::class] =
            function (PsrContainerInterface $container): ModelSchemaInfoInterface {
                $settings = $container->get(SettingsProviderInterface::class)->get(DataSettings::class);
                $data = $settings[DataSettingsInterface::KEY_MODELS_SCHEMA_INFO];

                return (new ModelSchemaInfo())->setData($data);
            };

        $container[Connection::class] = function (PsrContainerInterface $container): Connection {
            $settings = $container->get(SettingsProviderInterface::class)->get(DoctrineSettings::class);
            $params = array_filter([
                'driver' => $settings[DoctrineSettingsInterface::KEY_DRIVER] ?? null,
                'dbname' => $settings[DoctrineSettingsInterface::KEY_DATABASE_NAME] ?? null,
                'user' => $settings[DoctrineSettingsInterface::KEY_USER_NAME] ?? null,
                'password' => $settings[DoctrineSettingsInterface::KEY_PASSWORD] ?? null,
                'host' => $settings[DoctrineSettingsInterface::KEY_HOST] ?? null,
                'port' => $settings[DoctrineSettingsInterface::KEY_PORT] ?? null,
                'url' => $settings[DoctrineSettingsInterface::KEY_URL] ?? null,
                'memory' => $settings[DoctrineSettingsInterface::KEY_MEMORY] ?? null,
                'path' => $settings[DoctrineSettingsInterface::KEY_PATH] ?? null,
                'charset' => $settings[DoctrineSettingsInterface::KEY_CHARSET] ?? 'UTF8',
            ], function ($value) {
                return $value !== null;
            });
            $extra = $settings[DoctrineSettingsInterface::KEY_EXTRA] ?? [];

            $connection = DriverManager::getConnection($params + $extra);

            if (array_key_exists(DoctrineSettingsInterface::KEY_EXEC, $settings) === true &&
                is_array($toExec = $settings[DoctrineSettingsInterface::KEY_EXEC]) === true &&
                empty($toExec) === false
            ) {
                foreach ($toExec as $statement) {
                    $connection->executeStatement($statement);
                }
            }

            return $connection;
        };
    }
}
