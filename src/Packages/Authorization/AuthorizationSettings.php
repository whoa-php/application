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

namespace Whoa\Application\Packages\Authorization;

use Whoa\Application\Authorization\AuthorizationRulesLoader;
use Whoa\Contracts\Settings\Packages\AuthorizationSettingsInterface;
use ReflectionException;

use function assert;
use function glob;

/**
 * @package Whoa\Application
 */
abstract class AuthorizationSettings implements AuthorizationSettingsInterface
{
    /**
     * @inheritdoc
     * @throws ReflectionException
     */
    final public function get(array $appConfig): array
    {
        $defaults = $this->getSettings();

        $policiesFolder = $defaults[static::KEY_POLICIES_FOLDER] ?? null;
        $policiesFileMask = $defaults[static::KEY_POLICIES_FILE_MASK] ?? null;

        assert(
            $policiesFolder !== null && empty(glob($policiesFolder)) === false,
            "Invalid Policies folder `$policiesFolder`."
        );
        assert(empty($policiesFileMask) === false, "Invalid Policies file mask `$policiesFileMask`.");
        $policiesPath = $policiesFolder . DIRECTORY_SEPARATOR . $policiesFileMask;

        $topPolicyName = $defaults[static::KEY_TOP_POLICY_NAME];

        $loader = (new AuthorizationRulesLoader($policiesPath, $topPolicyName));

        return $defaults + [
                static::KEY_POLICIES_DATA => $loader->getRulesData(),
            ];
    }

    /**
     * @return array
     */
    protected function getSettings(): array
    {
        return [
            static::KEY_LOG_IS_ENABLED => true,
            static::KEY_TOP_POLICY_NAME => 'Application',
            static::KEY_POLICIES_FILE_MASK => '*.php',
        ];
    }
}
