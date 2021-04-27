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

namespace Whoa\Tests\Application\Config;

use Whoa\Application\Exceptions\AmbiguousSettingsException;
use Whoa\Application\Exceptions\NotRegisteredSettingsException;
use Whoa\Application\Settings\CacheSettingsProvider;
use Whoa\Application\Settings\FileSettingsProvider;
use Whoa\Tests\Application\CoreData\CoreDataTest;
use Whoa\Tests\Application\Data\Application\Settings\Application;
use Whoa\Tests\Application\Data\Config\MarkerInterfaceChild1;
use Whoa\Tests\Application\Data\Config\MarkerInterfaceTop;
use Whoa\Tests\Application\Data\Config\SampleSettingsAA;
use Whoa\Tests\Application\TestCase;
use ReflectionException;

/**
 * @package Whoa\Tests\Application
 */
class CacheSettingsProviderTest extends TestCase
{
    /**
     * Test loading from folder.
     * @throws ReflectionException
     */
    public function testLoadFromFolder(): void
    {
        $provider = $this->createProvider();
        $provider->unserialize($provider->serialize());

        $appSettings = [];
        $valuesA = (new SampleSettingsAA())->get($appSettings);

        $this->assertFalse($provider->has(MarkerInterfaceTop::class));
        $this->assertTrue($provider->isAmbiguous(MarkerInterfaceTop::class));

        $this->assertTrue($provider->has(MarkerInterfaceChild1::class));
        $this->assertFalse($provider->isAmbiguous(MarkerInterfaceChild1::class));
        $this->assertEquals($valuesA, $provider->get(MarkerInterfaceChild1::class));
    }

    /**
     * @return CacheSettingsProvider
     * @throws ReflectionException
     */
    private function createProvider(): CacheSettingsProvider
    {
        $appSettings = [];
        $fileSettingsProvider = (new FileSettingsProvider($appSettings))->load(
            __DIR__ . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR . 'Config'
            . DIRECTORY_SEPARATOR . '*.php'
        );

        $appConfig = new Application();
        $coreData = CoreDataTest::createCoreData();

        return (new CacheSettingsProvider())->setInstanceSettings($appConfig, $coreData, $fileSettingsProvider);
    }

    /**
     * @throws ReflectionException
     */
    public function testGetNotRegistered(): void
    {
        $this->expectException(NotRegisteredSettingsException::class);

        $this->createProvider()->get(static::class);
    }

    /**
     * @throws ReflectionException
     */
    public function testGetAmbiguous(): void
    {
        $this->expectException(AmbiguousSettingsException::class);

        $this->createProvider()->get(MarkerInterfaceTop::class);
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testGetAppConfig(): void
    {
        $this->assertNotEmpty($this->createProvider()->getApplicationConfiguration());
    }
}
