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

namespace Whoa\Tests\Application\CoreData;

use Whoa\Application\CoreSettings\CoreData;
use Whoa\Tests\Application\Data\CoreSettings\Providers\Provider1;
use Whoa\Tests\Application\TestCase;
use ReflectionException;

/**
 * @package Whoa\Tests\Application
 */
class CoreDataTest extends TestCase
{
    /**
     * Test compose settings.
     * @throws ReflectionException
     */
    public function testSettings(): void
    {
        $coreSettings = $this->createCoreData();

        $this->assertNotEmpty($coreSettings->get());
    }

    /**
     * @return CoreData
     */
    public static function createCoreData(): CoreData
    {
        return new CoreData(
            implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Data', 'CoreSettings', 'Routes', '*.php']),
            implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Data', 'CoreSettings', 'Configurators', '*.php']),
            [Provider1::class]
        );
    }
}
