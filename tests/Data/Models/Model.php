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

namespace Whoa\Tests\Application\Data\Models;

use Whoa\Contracts\Application\ModelInterface;

/**
 * @package Whoa\Tests\Application
 */
abstract class Model implements ModelInterface
{
    /** Table name */
    public const TABLE_NAME = null;

    /** Primary key */
    public const FIELD_ID = null;

    /** Field name */
    public const FIELD_CREATED_AT = 'created_at';

    /** Field name */
    public const FIELD_UPDATED_AT = 'updated_at';

    /** Field name */
    public const FIELD_DELETED_AT = 'deleted_at';

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return static::TABLE_NAME;
    }

    /**
     * @inheritdoc
     */
    public static function getPrimaryKeyName(): string
    {
        return static::FIELD_ID;
    }


    /**
     * @inheritdoc
     */
    public static function getRawAttributes(): array
    {
        return [];
    }

    public static function getVirtualAttributes(): array
    {
        return [];
    }
}
