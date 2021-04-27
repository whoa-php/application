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

namespace Whoa\Application\Packages\Csrf;

use ArrayAccess;
use Exception;
use Whoa\Application\Contracts\Csrf\CsrfTokenStorageInterface;

use function array_key_exists;
use function array_slice;
use function asort;
use function assert;
use function bin2hex;
use function count;
use function random_bytes;
use function time;

/**
 * @package Whoa\Application
 */
class CsrfTokenStorage implements CsrfTokenStorageInterface
{
    /**
     * Number of random bytes in a token.
     */
    protected const TOKEN_BYTE_LENGTH = 16;

    /**
     * @var ArrayAccess
     */
    private ArrayAccess $sessionStorage;

    /**
     * @var string
     */
    private string $tokenStorageKey;

    /**
     * @var null|int
     */
    private ?int $maxTokens = null;

    /**
     * @var int
     */
    private int $maxTokensGcThreshold;

    /**
     * @param ArrayAccess $sessionStorage
     * @param string $tokenStorageKey
     * @param int|null $maxTokens
     * @param int $maxTokensGcThreshold
     */
    public function __construct(
        ArrayAccess $sessionStorage,
        string $tokenStorageKey,
        ?int $maxTokens,
        int $maxTokensGcThreshold
    ) {
        $this->setSessionStorage($sessionStorage)
            ->setTokenStorageKey($tokenStorageKey)
            ->setMaxTokens($maxTokens)
            ->setMaxTokensGcThreshold($maxTokensGcThreshold);
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function create(): string
    {
        $tokenStorage = $this->getTokenStorage();
        $value = $this->createTokenValue();
        $timestamp = $this->createTokenTimestamp();

        $tokenStorage[$value] = $timestamp;

        // check if we should limit number to stored tokens
        $maxTokens = $this->getMaxTokens();
        if ($maxTokens !== null &&
            count($tokenStorage) > $maxTokens + $this->getMaxTokensGcThreshold()
        ) {
            // sort by timestamp and take last $maxTokens
            asort($tokenStorage, SORT_NUMERIC);
            $tokenStorage = array_slice($tokenStorage, -$maxTokens, null, true);
            // minus means count from the end ---------^
        }

        $this->setTokenStorage($tokenStorage);

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function check(string $token): bool
    {
        $tokenStorage = $this->getTokenStorage();
        $tokenFound = array_key_exists($token, $tokenStorage);
        if ($tokenFound === true) {
            // remove the token so it cannot be used again
            unset($tokenStorage[$token]);
            $this->setTokenStorage($tokenStorage);
        }

        return $tokenFound;
    }

    /**
     * @return ArrayAccess
     */
    protected function getSessionStorage(): ArrayAccess
    {
        return $this->sessionStorage;
    }

    /**
     * @param ArrayAccess $sessionStorage
     * @return self
     */
    protected function setSessionStorage(ArrayAccess $sessionStorage): self
    {
        $this->sessionStorage = $sessionStorage;

        return $this;
    }

    /**
     * @return string
     */
    protected function getTokenStorageKey(): string
    {
        return $this->tokenStorageKey;
    }

    /**
     * @param string $tokenStorageKey
     * @return self
     */
    protected function setTokenStorageKey(string $tokenStorageKey): self
    {
        assert(empty($tokenStorageKey) === false);

        $this->tokenStorageKey = $tokenStorageKey;

        return $this;
    }

    /**
     * @return int|null
     */
    protected function getMaxTokens(): ?int
    {
        return $this->maxTokens;
    }

    /**
     * @param int|null $maxTokens
     * @return self
     */
    protected function setMaxTokens(?int $maxTokens): self
    {
        assert($maxTokens === null || $maxTokens > 0);

        $this->maxTokens = $maxTokens > 0 ? $maxTokens : null;

        return $this;
    }

    /**
     * @return int
     */
    protected function getMaxTokensGcThreshold(): int
    {
        return $this->maxTokensGcThreshold;
    }

    /**
     * @param int $maxTokensGcThreshold
     * @return self
     */
    protected function setMaxTokensGcThreshold(int $maxTokensGcThreshold): self
    {
        assert($maxTokensGcThreshold >= 0);

        $this->maxTokensGcThreshold = max($maxTokensGcThreshold, 0);

        return $this;
    }

    /**
     * @return string
     * @throws Exception
     */
    protected function createTokenValue(): string
    {
        return bin2hex(random_bytes(static::TOKEN_BYTE_LENGTH));
    }

    /**
     * Additional information that would be stored with a token. For example, could be creation timestamp.
     * @return int
     */
    protected function createTokenTimestamp(): int
    {
        return time();
    }

    /**
     * @return array
     */
    protected function getTokenStorage(): array
    {
        $sessionStorage = $this->getSessionStorage();
        $storageKey = $this->getTokenStorageKey();

        return $sessionStorage->offsetExists($storageKey) === true ? $sessionStorage->offsetGet($storageKey) : [];
    }

    /**
     * Replace whole token storage.
     * @param array $tokenStorage
     * @return self
     */
    protected function setTokenStorage(array $tokenStorage): self
    {
        $this->getSessionStorage()->offsetSet($this->getTokenStorageKey(), $tokenStorage);

        return $this;
    }
}
