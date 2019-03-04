<?php declare(strict_types=1);

namespace Limoncello\Validation\Rules\Comparisons;

/**
 * Copyright 2015-2019 info@neomerx.com
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

use Limoncello\Validation\Contracts\Errors\ErrorCodes;
use Limoncello\Validation\Contracts\Execution\ContextInterface;
use Limoncello\Validation\I18n\Messages;
use function assert;
use function is_numeric;

/**
 * @package Limoncello\Validation
 */
final class NumericBetween extends BaseTwoValueComparision
{
    /**
     * @param mixed $lowerValue
     * @param mixed $upperValue
     */
    public function __construct($lowerValue, $upperValue)
    {
        assert(is_numeric($lowerValue) === true && is_numeric($upperValue) === true && $lowerValue <= $upperValue);
        parent::__construct(
            $lowerValue,
            $upperValue,
            ErrorCodes::NUMERIC_BETWEEN,
            Messages::NUMERIC_BETWEEN,
            [$lowerValue, $upperValue]
        );
    }

    /**
     * @inheritdoc
     */
    public static function compare($value, ContextInterface $context): bool
    {
        assert(is_numeric($value) === true);
        $result =
            is_numeric($value) === true &&
            static::readLowerValue($context) <= $value && $value <= static::readUpperValue($context);

        return $result;
    }
}
