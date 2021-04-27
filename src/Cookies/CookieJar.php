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

use Whoa\Application\Exceptions\InvalidArgumentException;
use Whoa\Contracts\Cookies\CookieInterface;
use Whoa\Contracts\Cookies\CookieJarInterface;

use function array_key_exists;

/**
 * @package Whoa\Application
 */
class CookieJar implements CookieJarInterface
{
    /**
     * @var CookieInterface[]
     */
    private array $cookies;

    /**
     * @var string
     */
    private string $defaultPath;

    /**
     * @var string
     */
    private string $defaultDomain;

    /**
     * @var bool
     */
    private bool $defaultIsSecure;

    /**
     * @var bool
     */
    private bool $defaultIsHttpOnly;

    /**
     * @var bool
     */
    private bool $defaultIsRaw;

    /**
     * @param string $defaultPath
     * @param string $defaultDomain
     * @param bool $defaultIsSecure
     * @param bool $defaultIsHttpOnly
     * @param bool $defaultIsRaw
     */
    public function __construct(
        string $defaultPath,
        string $defaultDomain,
        bool $defaultIsSecure,
        bool $defaultIsHttpOnly,
        bool $defaultIsRaw
    ) {
        $this->cookies = [];
        $this->defaultPath = $defaultPath;
        $this->defaultDomain = $defaultDomain;
        $this->defaultIsSecure = $defaultIsSecure;
        $this->defaultIsHttpOnly = $defaultIsHttpOnly;
        $this->defaultIsRaw = $defaultIsRaw;
    }


    /**
     * @inheritdoc
     */
    public function create(string $cookieName): CookieInterface
    {
        if ($this->has($cookieName) === true) {
            throw new InvalidArgumentException($cookieName);
        }

        $cookie = new Cookie(
            $cookieName,
            '',
            0,
            $this->getDefaultPath(),
            $this->getDefaultDomain(),
            $this->getDefaultIsSecure(),
            $this->getDefaultIsHttpOnly(),
            $this->getDefaultIsRaw()
        );

        $this->cookies[$cookieName] = $cookie;

        return $cookie;
    }

    /**
     * @inheritdoc
     */
    public function has(string $cookieName): bool
    {
        return array_key_exists($cookieName, $this->cookies);
    }

    /**
     * @inheritdoc
     */
    public function get(string $cookieName): CookieInterface
    {
        return $this->cookies[$cookieName];
    }

    /**
     * @inheritdoc
     */
    public function delete(string $cookieName): CookieJarInterface
    {
        unset($this->cookies[$cookieName]);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAll(): iterable
    {
        /** @var iterable $result */
        return $this->cookies;
    }

    /**
     * @return string
     */
    protected function getDefaultPath(): string
    {
        return $this->defaultPath;
    }

    /**
     * @return string
     */
    protected function getDefaultDomain(): string
    {
        return $this->defaultDomain;
    }

    /**
     * @return bool
     */
    protected function getDefaultIsSecure(): bool
    {
        return $this->defaultIsSecure;
    }

    /**
     * @return bool
     */
    protected function getDefaultIsHttpOnly(): bool
    {
        return $this->defaultIsHttpOnly;
    }

    /**
     * @return bool
     */
    protected function getDefaultIsRaw(): bool
    {
        return $this->defaultIsRaw;
    }
}
