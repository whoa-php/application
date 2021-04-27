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

namespace Whoa\Tests\Application\Packages\Application;

use Whoa\Application\Packages\Application\Application;
use Whoa\Application\Settings\CacheSettingsProvider;
use Whoa\Application\Settings\InstanceSettingsProvider;
use Whoa\Tests\Application\CoreData\CoreDataTest;
use Whoa\Tests\Application\Data\Application\Settings\Application as ApplicationConfiguration;
use Whoa\Tests\Application\TestCase;
use ReflectionException;

/**
 * @package Whoa\Tests\Application
 */
class ApplicationTest extends TestCase
{
    /**
     * Test create container.
     * @throws ReflectionException
     */
    public function testCreateContainerOnTheFly(): void
    {
        $application = $this->createApplication();

        $this->assertNotNull($application->createContainer('SOME_METHOD', '/some_path'));
    }

    /**
     * Test create container.
     * @throws ReflectionException
     */
    public function testCreateContainerFromCache(): void
    {
        /** @var callable $settingCacheMethod */
        $settingCacheMethod = [static::class, 'getCachedSettings'];
        $application = $this->createApplication($settingCacheMethod);

        $this->assertNotNull($application->createContainer('SOME_METHOD', '/some_path'));
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    public static function getCachedSettings(): array
    {
        $appConfig = new ApplicationConfiguration();
        $provider = new InstanceSettingsProvider($appConfig->get());

        $coreData = CoreDataTest::createCoreData();
        return (new CacheSettingsProvider())->setInstanceSettings($appConfig, $coreData, $provider)->serialize();
    }

    /**
     * @param null|string|array|callable $settingCacheMethod
     * @return Application
     */
    private function createApplication($settingCacheMethod = null): Application
    {
        $settingsPath = implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', 'Data', 'Application', 'Settings', '*.php']);
        return new Application($settingsPath, $settingCacheMethod);
    }
}
