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
class Comment extends Model
{
    /** @inheritdoc */
    public const TABLE_NAME = 'comments';

    /** @inheritdoc */
    public const FIELD_ID = 'id_comment';

    /** Field name */
    public const FIELD_ID_POST = 'id_post_fk';

    /** Field name */
    public const FIELD_ID_USER = 'id_user_fk';

    /** Relationship name */
    public const REL_POST = 'post';

    /** Relationship name */
    public const REL_USER = 'user';

    /** Relationship name */
    public const REL_EMOTIONS = 'emotions';

    /** Field name */
    public const FIELD_TEXT = 'text';

    /** Length constant */
    public const LENGTH_TEXT = 255;

    /**
     * @inheritdoc
     */
    public static function getAttributeTypes(): array
    {
        return [
            self::FIELD_ID => Types::INTEGER,
            self::FIELD_ID_POST => Types::INTEGER,
            self::FIELD_ID_USER => Types::INTEGER,
            self::FIELD_TEXT => Types::STRING,
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
            self::FIELD_TEXT => self::LENGTH_TEXT,
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getRelationships(): array
    {
        return [
            RelationshipTypes::BELONGS_TO => [
                self::REL_POST => [
                    Post::class,
                    self::FIELD_ID_POST,
                    Post::REL_COMMENTS,
                ],
                self::REL_USER => [
                    User::class,
                    self::FIELD_ID_USER,
                    User::REL_COMMENTS,
                ],
            ],
            RelationshipTypes::BELONGS_TO_MANY => [
                self::REL_EMOTIONS => [
                    Emotion::class,
                    CommentEmotion::TABLE_NAME,
                    CommentEmotion::FIELD_ID_COMMENT,
                    CommentEmotion::FIELD_ID_EMOTION,
                    Emotion::REL_COMMENTS,
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getRawAttributes(): array
    {
        $usersTable = User::TABLE_NAME;
        $userId = User::FIELD_ID;
        $userName = User::FIELD_FIRST_NAME;
        $authorId = self::FIELD_ID_USER;

        return [
                "(SELECT $userName FROM $usersTable WHERE $userId = $authorId) AS user_name",
            ] + parent::getRawAttributes();
    }
}
