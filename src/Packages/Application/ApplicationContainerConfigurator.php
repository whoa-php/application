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

namespace Whoa\Application\Packages\Application;

use Whoa\Application\Commands\CommandStorage;
use Whoa\Common\Reflection\ClassIsTrait;
use Whoa\Contracts\Application\ApplicationConfigurationInterface as S;
use Whoa\Contracts\Application\CacheSettingsProviderInterface;
use Whoa\Contracts\Application\ContainerConfiguratorInterface;
use Whoa\Contracts\Commands\CommandInterface;
use Whoa\Contracts\Commands\CommandStorageInterface;
use Whoa\Contracts\Container\ContainerInterface as WhoaContainerInterface;
use Whoa\Contracts\Provider\ProvidesCommandsInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;

/**
 * @package Whoa\Application
 */
class ApplicationContainerConfigurator implements ContainerConfiguratorInterface
{
    /** @var callable */
    public const CONFIGURATOR = [self::class, self::CONTAINER_METHOD_NAME];

    /**
     * @inheritdoc
     */
    public static function configureContainer(WhoaContainerInterface $container): void
    {
        $container[CommandStorageInterface::class] =
            function (PsrContainerInterface $container): CommandStorageInterface {
                $creator = new class {
                    use ClassIsTrait;

                    /**
                     * @param string $commandsPath
                     * @param array $providerClasses
                     *
                     * @return CommandStorageInterface
                     */
                    public function createCommandStorage(
                        string $commandsPath,
                        array $providerClasses
                    ): CommandStorageInterface {
                        $storage = new CommandStorage();

                        $interfaceName = CommandInterface::class;
                        foreach ($this->selectClasses($commandsPath, $interfaceName) as $commandClass) {
                            $storage->add($commandClass);
                        }

                        $interfaceName = ProvidesCommandsInterface::class;
                        foreach ($this->selectClassImplements($providerClasses, $interfaceName) as $providerClass) {
                            /** @var ProvidesCommandsInterface $providerClass */
                            foreach ($providerClass::getCommands() as $commandClass) {
                                $storage->add($commandClass);
                            }
                        }

                        return $storage;
                    }
                };

                /** @var CacheSettingsProviderInterface $provider */
                $provider = $container->get(CacheSettingsProviderInterface::class);
                $appConfig = $provider->getApplicationConfiguration();

                $providerClasses = $appConfig[S::KEY_PROVIDER_CLASSES];
                $commandsFolder = $appConfig[S::KEY_COMMANDS_FOLDER];
                $commandsFileMask = $appConfig[S::KEY_COMMANDS_FILE_MASK] ?? '*.php';
                $commandsPath = $commandsFolder . DIRECTORY_SEPARATOR . $commandsFileMask;

                return $creator->createCommandStorage($commandsPath, $providerClasses);
            };
    }
}
