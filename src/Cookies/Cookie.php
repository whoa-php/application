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

use DateTimeInterface;
use Whoa\Application\Exceptions\InvalidArgumentException;
use Whoa\Contracts\Cookies\CookieInterface;

/**
 * @package Whoa\Application
 */
class Cookie implements CookieInterface
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var string
     */
    private string $value;

    /**
     * @var int
     */
    private int $expire;

    /**
     * @var string
     */
    private string $path;

    /**
     * @var string
     */
    private string $domain;

    /**
     * @var bool
     */
    private bool $isSecure;

    /**
     * @var bool
     */
    private bool $isHttpOnly;

    /**
     * @var bool
     */
    private bool $isRaw;

    /**
     * @param string $name
     * @param string $value
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @param bool $isSecure
     * @param bool $isHttpOnly
     * @param bool $isRaw
     */
    public function __construct(
        string $name,
        string $value,
        int $expire,
        string $path,
        string $domain,
        bool $isSecure,
        bool $isHttpOnly,
        bool $isRaw
    ) {
        $this->name = $name;
        $this
            ->setValue($value)
            ->setExpiresAtUnixTime($expire)
            ->setPath($path)
            ->setDomain($domain);

        $isSecure === true ? $this->setSendOnlyOverSecureConnection() : $this->setSendOverAnyConnection();
        $isHttpOnly === true ? $this->setAccessibleOnlyThroughHttp() : $this->setAccessibleThroughHttpAndScripts();
        $isRaw === true ? $this->setAsRaw() : $this->setAsNotRaw();
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @inheritdoc
     */
    public function setValue(string $value): CookieInterface
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getExpiresAtUnixTime(): int
    {
        return $this->expire;
    }

    /**
     * @inheritdoc
     */
    public function setExpiresAtUnixTime(int $unixTimestamp): CookieInterface
    {
        if ($unixTimestamp < 0) {
            throw new InvalidArgumentException('Timestamp cannot be negative.');
        }

        $this->expire = $unixTimestamp;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setExpiresInSeconds(int $seconds): CookieInterface
    {
        return $this->setExpiresAtUnixTime(time() + (int)max(0, $seconds));
    }

    /**
     * @inheritdoc
     */
    public function setExpiresAtDataTime(DateTimeInterface $dateTime): CookieInterface
    {
        return $this->setExpiresAtUnixTime($dateTime->getTimestamp());
    }

    /**
     * @inheritdoc
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @inheritdoc
     */
    public function setPath(string $path): CookieInterface
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @inheritdoc
     */
    public function setDomain(string $domain): CookieInterface
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isSendOnlyOverSecureConnection(): bool
    {
        return $this->isSecure;
    }

    /**
     * @inheritdoc
     */
    public function setSendOnlyOverSecureConnection(): CookieInterface
    {
        $this->isSecure = true;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isSendOverAnyConnection(): bool
    {
        return !$this->isSecure;
    }

    /**
     * @inheritdoc
     */
    public function setSendOverAnyConnection(): CookieInterface
    {
        $this->isSecure = false;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isAccessibleOnlyThroughHttp(): bool
    {
        return $this->isHttpOnly;
    }

    /**
     * @inheritdoc
     */
    public function setAccessibleOnlyThroughHttp(): CookieInterface
    {
        $this->isHttpOnly = true;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isAccessibleThroughHttpAndScripts(): bool
    {
        return !$this->isHttpOnly;
    }

    /**
     * @inheritdoc
     */
    public function setAccessibleThroughHttpAndScripts(): CookieInterface
    {
        $this->isHttpOnly = false;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isRaw(): bool
    {
        return $this->isRaw;
    }

    /**
     * @inheritdoc
     */
    public function setAsRaw(): CookieInterface
    {
        $this->isRaw = true;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isNotRaw(): bool
    {
        return !$this->isRaw;
    }

    /**
     * @inheritdoc
     */
    public function setAsNotRaw(): CookieInterface
    {
        $this->isRaw = false;

        return $this;
    }
}
