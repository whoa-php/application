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

namespace Whoa\Application\Packages\L10n;

use Whoa\Contracts\Application\ApplicationConfigurationInterface as A;
use Whoa\Contracts\Provider\ProvidesMessageResourcesInterface;
use Whoa\Contracts\Settings\Packages\L10nSettingsInterface;
use Whoa\l10n\Messages\FileBundleEncoder;

use function assert;
use function class_implements;
use function glob;
use function in_array;
use function is_string;

/**
 * @package Whoa\Application
 */
abstract class L10nSettings implements L10nSettingsInterface
{
    /**
     * @var array
     */
    private array $appConfig;

    /**
     * @inheritdoc
     */
    final public function get(array $appConfig): array
    {
        $this->appConfig = $appConfig;

        $defaults = $this->getSettings();

        $defaultLocale = $defaults[static::KEY_DEFAULT_LOCALE] ?? null;
        assert(
            is_string($defaultLocale) === true && empty($defaultLocale) === false,
            "Invalid default locale `$defaultLocale`."
        );

        $localesFolder = $defaults[static::KEY_LOCALES_FOLDER] ?? null;
        assert(
            $localesFolder !== null && empty(glob($localesFolder)) === false,
            "Invalid Locales folder `$localesFolder`."
        );

        $bundleEncoder = new FileBundleEncoder($this->getMessageDescriptionsFromProviders(), (string)$localesFolder);

        return $defaults + [
                static::KEY_LOCALES_DATA => $bundleEncoder->getStorageData((string)$defaultLocale),
            ];
    }

    /**
     * @return array
     */
    protected function getSettings(): array
    {
        return [
            static::KEY_DEFAULT_LOCALE => 'en',
        ];
    }

    /**
     * @return array
     */
    protected function getAppConfig(): array
    {
        return $this->appConfig;
    }

    /**
     *
     * @return iterable
     */
    private function getMessageDescriptionsFromProviders(): iterable
    {
        $providerClasses = $this->getAppConfig()[A::KEY_PROVIDER_CLASSES] ?? [];
        foreach ($providerClasses as $class) {
            if (in_array(ProvidesMessageResourcesInterface::class, class_implements($class)) === true) {
                /** @var ProvidesMessageResourcesInterface $class */
                foreach ($class::getMessageDescriptions() as $messageDescription) {
                    yield $messageDescription;
                }
            }
        }
    }
}
