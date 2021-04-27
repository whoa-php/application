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

namespace Whoa\Tests\Application\Cookies;

use DateInterval;
use DateTime;
use Exception;
use Whoa\Application\Cookies\Cookie;
use Whoa\Application\Exceptions\InvalidArgumentException;
use Whoa\Tests\Application\TestCase;

/**
 * @package Whoa\Tests\Application
 */
class CookieTest extends TestCase
{
    /**
     * Test basic cookie properties.
     * @throws Exception
     */
    public function testBasicCookieProperties(): void
    {
        $name = 'name';
        $value = 'value';
        $expire = 123;
        $path = '/path';
        $domain = 'domain';
        $isSecure = true;
        $isHttpOnly = true;
        $isRaw = true;

        $cookie = new Cookie($name, $value, $expire, $path, $domain, $isSecure, $isHttpOnly, $isRaw);

        $this->assertEquals($name, $cookie->getName());
        $this->assertEquals($value, $cookie->getValue());
        $this->assertEquals($expire, $cookie->getExpiresAtUnixTime());
        $this->assertEquals($path, $cookie->getPath());
        $this->assertEquals($domain, $cookie->getDomain());
        $this->assertEquals($isSecure, $cookie->isSendOnlyOverSecureConnection());
        $this->assertEquals(!$isSecure, $cookie->isSendOverAnyConnection());
        $this->assertEquals($isHttpOnly, $cookie->isAccessibleOnlyThroughHttp());
        $this->assertEquals(!$isHttpOnly, $cookie->isAccessibleThroughHttpAndScripts());
        $this->assertEquals($isRaw, $cookie->isRaw());
        $this->assertEquals(!$isRaw, $cookie->isNotRaw());

        $isSecure ? $cookie->setSendOverAnyConnection() : $cookie->setSendOnlyOverSecureConnection();
        $this->assertEquals(!$isSecure, $cookie->isSendOnlyOverSecureConnection());
        $this->assertEquals($isSecure, $cookie->isSendOverAnyConnection());

        $isHttpOnly ? $cookie->setAccessibleThroughHttpAndScripts() : $cookie->setAccessibleOnlyThroughHttp();
        $this->assertEquals(!$isHttpOnly, $cookie->isAccessibleOnlyThroughHttp());
        $this->assertEquals($isHttpOnly, $cookie->isAccessibleThroughHttpAndScripts());

        $isRaw ? $cookie->setAsNotRaw() : $cookie->setAsRaw();
        $this->assertEquals(!$isRaw, $cookie->isRaw());
        $this->assertEquals($isRaw, $cookie->isNotRaw());

        $now = new DateTime();
        $nowInSec = $now->getTimestamp();

        $cookie->setExpiresInSeconds(10);
        // setExpiresInSeconds internally takes current time so there is a tiny chance that it will be
        // 1 second difference.
        $this->assertLessThanOrEqual(1, $cookie->getExpiresAtUnixTime() - ($nowInSec + 10));

        $in100seconds = $now->add(new DateInterval('PT100S'));
        $cookie->setExpiresAtDataTime($in100seconds);
        $this->assertEquals($nowInSec + 100, $cookie->getExpiresAtUnixTime());

        try {
            $cookie->setExpiresAtUnixTime(-1);
        } catch (InvalidArgumentException $exception) {
        }
        $this->assertTrue(isset($exception));
    }
}
