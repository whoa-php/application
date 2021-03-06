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

use Doctrine\DBAL\Types\Types;
use Whoa\Contracts\Data\RelationshipTypes;

/**
 * @package Whoa\Tests\Application
 */
class User extends Model
{
    /** @inheritdoc */
    public const TABLE_NAME = 'users';

    /** @inheritdoc */
    public const FIELD_ID = 'id_user';

    /** Relationship name */
    public const REL_ROLE = 'role';

    /** Relationship name */
    public const REL_AUTHORED_POSTS = 'authored_posts';

    /** Relationship name */
    public const REL_EDITOR_POSTS = 'editor_posts';

    /** Relationship name */
    public const REL_COMMENTS = 'comments';

    /** Field name */
    public const FIELD_ID_ROLE = 'id_role_fk';

    /** Field name */
    public const FIELD_TITLE = 'title';

    /** Field name */
    public const FIELD_FIRST_NAME = 'first_name';

    /** Field name */
    public const FIELD_LAST_NAME = 'last_name';

    /** Field name */
    public const FIELD_EMAIL = 'email';

    /** Field name */
    public const FIELD_IS_ACTIVE = 'is_active';

    /** Field name */
    public const FIELD_PASSWORD_HASH = 'password_hash';

    /** Field name */
    public const FIELD_LANGUAGE = 'language';

    /** Field name */
    public const FIELD_API_TOKEN = 'api_token';

    /**
     * @inheritdoc
     */
    public static function getAttributeTypes(): array
    {
        return [
            self::FIELD_ID => Types::INTEGER,
            self::FIELD_ID_ROLE => Types::INTEGER,
            self::FIELD_TITLE => Types::STRING,
            self::FIELD_FIRST_NAME => Types::STRING,
            self::FIELD_LAST_NAME => Types::STRING,
            self::FIELD_EMAIL => Types::STRING,
            self::FIELD_IS_ACTIVE => Types::BOOLEAN,
            self::FIELD_PASSWORD_HASH => Types::STRING,
            self::FIELD_LANGUAGE => Types::STRING,
            self::FIELD_API_TOKEN => Types::STRING,
            self::FIELD_CREATED_AT => Types::DATETIME_IMMUTABLE,
            self::FIELD_UPDATED_AT => Types::DATETIME_IMMUTABLE,
            self::FIELD_DELETED_AT => Types::DATETIME_IMMUTABLE,
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getAttributeLengths(): array
    {
        return [
            self::FIELD_TITLE => 255,
            self::FIELD_FIRST_NAME => 255,
            self::FIELD_LAST_NAME => 255,
            self::FIELD_EMAIL => 255,
            self::FIELD_PASSWORD_HASH => 255,
            self::FIELD_LANGUAGE => 255,
            self::FIELD_API_TOKEN => 255,
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getRelationships(): array
    {
        return [
            RelationshipTypes::BELONGS_TO => [
                self::REL_ROLE => [Role::class, self::FIELD_ID_ROLE, Role::REL_USERS],
            ],
            RelationshipTypes::HAS_MANY => [
                self::REL_AUTHORED_POSTS => [Post::class, Post::FIELD_ID_USER, Post::REL_USER],
                self::REL_EDITOR_POSTS => [Post::class, Post::FIELD_ID_EDITOR, Post::REL_EDITOR],
                self::REL_COMMENTS => [Comment::class, Comment::FIELD_ID_USER, Comment::REL_USER],
            ],
        ];
    }
}
