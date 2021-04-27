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

namespace Whoa\Tests\Application\Data\Config;

use Whoa\Tests\Application\Data\Config\MarkerInterfaceChild11And21 as MIC1121;
use Whoa\Tests\Application\Data\Config\MarkerInterfaceChild21 as MIC21;
use Whoa\Tests\Application\Data\Config\MarkerInterfaceStandalone as MIS;

/**
 * @package Whoa\Tests\Application
 */
class SampleSettingsBB extends SampleSettingsB implements MIC21, MIS, MIC1121
{
    /**
     * @var string
     */
    private string $dummyDefaultParam;

    /**
     * @param string $dummyDefaultParam
     */
    public function __construct(string $dummyDefaultParam = __CLASS__)
    {
        $this->dummyDefaultParam = $dummyDefaultParam;
    }

    /**
     * @inheritdoc
     */
    public function get(array $appConfig): array
    {
        return ['value' => 'BB'] + parent::get($appConfig);
    }
}
