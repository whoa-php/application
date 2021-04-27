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

namespace Whoa\Application\Settings;

use Whoa\Application\CoreSettings\CoreData;
use Whoa\Application\Exceptions\AmbiguousSettingsException;
use Whoa\Application\Exceptions\NotRegisteredSettingsException;
use Whoa\Contracts\Application\ApplicationConfigurationInterface;
use Whoa\Contracts\Application\CacheSettingsProviderInterface;
use ReflectionException;

use function array_key_exists;

/**
 * @package Whoa\Application
 */
class CacheSettingsProvider implements CacheSettingsProviderInterface
{
    /** Internal data index */
    public const KEY_APPLICATION_CONFIGURATION = 0;

    /** Internal data index */
    public const KEY_CORE_DATA = self::KEY_APPLICATION_CONFIGURATION + 1;

    /** Internal data index */
    public const KEY_SETTINGS_MAP = self::KEY_CORE_DATA + 1;

    /** Internal data index */
    public const KEY_SETTINGS_DATA = self::KEY_SETTINGS_MAP + 1;

    /** Internal data index */
    public const KEY_AMBIGUOUS_MAP = self::KEY_SETTINGS_DATA + 1;

    /**
     * @var array
     */
    private array $appConfig = [];

    /**
     * @var array
     */
    private array $coreData = [];

    /**
     * @var array
     */
    private array $settingsMap = [];

    /**
     * @var array
     */
    private array $settingsData = [];

    /**
     * @var array
     */
    private array $ambiguousMap = [];

    /**
     * @inheritdoc
     */
    public function get(string $className): array
    {
        if ($this->has($className) === false) {
            if (array_key_exists($className, $this->ambiguousMap) === true) {
                throw new AmbiguousSettingsException($className);
            }
            throw new NotRegisteredSettingsException($className);
        }

        return $this->settingsData[$this->settingsMap[$className]];
    }

    /**
     * @inheritdoc
     */
    public function getApplicationConfiguration(): array
    {
        return $this->appConfig;
    }

    /**
     * @inheritdoc
     */
    public function getCoreData(): array
    {
        return $this->coreData;
    }

    /**
     * @inheritdoc
     */
    public function has(string $className): bool
    {
        return array_key_exists($className, $this->settingsMap);
    }

    /**
     * @inheritdoc
     */
    public function isAmbiguous(string $className): bool
    {
        return array_key_exists($className, $this->ambiguousMap);
    }

    /**
     * @param ApplicationConfigurationInterface $appConfig
     * @param CoreData $coreData
     * @param InstanceSettingsProvider $provider
     * @return self
     * @throws ReflectionException
     */
    public function setInstanceSettings(
        ApplicationConfigurationInterface $appConfig,
        CoreData $coreData,
        InstanceSettingsProvider $provider
    ): self {
        $this->unserialize([
            static::KEY_APPLICATION_CONFIGURATION => $appConfig->get(),
            static::KEY_CORE_DATA => $coreData->get(),
            static::KEY_SETTINGS_MAP => $provider->getSettingsMap(),
            static::KEY_SETTINGS_DATA => $provider->getSettingsData(),
            static::KEY_AMBIGUOUS_MAP => $provider->getAmbiguousMap(),
        ]);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function serialize(): array
    {
        return [
            static::KEY_APPLICATION_CONFIGURATION => $this->appConfig,
            static::KEY_CORE_DATA => $this->coreData,
            static::KEY_SETTINGS_MAP => $this->settingsMap,
            static::KEY_SETTINGS_DATA => $this->settingsData,
            static::KEY_AMBIGUOUS_MAP => $this->ambiguousMap,
        ];
    }

    /**
     * @inheritdoc
     */
    public function unserialize(array $serialized): void
    {
        [
            static::KEY_APPLICATION_CONFIGURATION => $this->appConfig,
            static::KEY_CORE_DATA => $this->coreData,
            static::KEY_SETTINGS_MAP => $this->settingsMap,
            static::KEY_SETTINGS_DATA => $this->settingsData,
            static::KEY_AMBIGUOUS_MAP => $this->ambiguousMap,
        ] = $serialized;
    }
}
