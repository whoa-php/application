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

namespace Whoa\Application\Authorization;

/**
 * @package Whoa\Application
 */
interface RequestProperties
{
    /** Request key */
    public const REQ_FIRST = 0;

    /** Request key */
    public const REQ_ACTION = self::REQ_FIRST;

    /** Request key */
    public const REQ_RESOURCE_TYPE = self::REQ_ACTION + 1;

    /** Request key */
    public const REQ_RESOURCE_IDENTITY = self::REQ_RESOURCE_TYPE + 1;

    /** Request key */
    public const REQ_RESOURCE_ATTRIBUTES = self::REQ_RESOURCE_IDENTITY + 1;

    /** Request key */
    public const REQ_RESOURCE_RELATIONSHIPS = self::REQ_RESOURCE_ATTRIBUTES + 1;

    /** Request key */
    public const REQ_LAST = self::REQ_RESOURCE_RELATIONSHIPS;
}
