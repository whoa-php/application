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

namespace Whoa\Application\Cookies;

use Whoa\Application\Contracts\Cookie\CookieFunctionsInterface;

/**
 * @package Whoa\Application
 */
class CookieFunctions implements CookieFunctionsInterface
{
    /** @var callable */
    private $setCookieCallable = '\setcookie';

    /** @var callable */
    private $setRawCookieCallable = '\setrawcookie';

    /**
     * @inheritdoc
     */
    public function getWriteCookieCallable(): callable
    {
        return $this->setCookieCallable;
    }

    /**
     * @inheritdoc
     */
    public function setWriteCookieCallable(callable $callable): CookieFunctionsInterface
    {
        $this->setCookieCallable = $callable;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getWriteRawCookieCallable(): callable
    {
        return $this->setRawCookieCallable;
    }

    /**
     * @inheritdoc
     */
    public function setWriteRawCookieCallable(callable $callable): CookieFunctionsInterface
    {
        $this->setRawCookieCallable = $callable;

        return $this;
    }
}
