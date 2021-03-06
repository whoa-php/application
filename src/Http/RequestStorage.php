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

namespace Whoa\Application\Http;

use Whoa\Contracts\Http\RequestStorageInterface;
use Psr\Http\Message\ServerRequestInterface;

use function assert;

/**
 * @package Whoa\Application\Http
 */
class RequestStorage implements RequestStorageInterface
{
    /**
     * @var ServerRequestInterface|null
     */
    private ?ServerRequestInterface $request = null;

    /**
     * @inheritdoc
     */
    public function get(): ServerRequestInterface
    {
        assert($this->has(), 'Request has not been assigned yet.');

        return $this->request;
    }

    /**
     * @inheritdoc
     */
    public function set(ServerRequestInterface $request): RequestStorageInterface
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function has(): bool
    {
        return $this->request !== null;
    }
}
