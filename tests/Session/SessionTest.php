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

namespace Whoa\Tests\Application\Session;

use ArrayIterator;
use Whoa\Application\Contracts\Session\SessionFunctionsInterface;
use Whoa\Application\Session\Session;
use Whoa\Application\Session\SessionFunctions;
use Whoa\Tests\Application\TestCase;
use Mockery;
use Mockery\Mock;

/**
 * @package Whoa\Tests\Application
 */
class SessionTest extends TestCase
{
    public function testSessionFunctions(): void
    {
        $functions = new SessionFunctions();

        $this->assertTrue(is_callable($functions->getRetrieveCallable()));
        $this->assertTrue(is_callable($functions->getPutCallable()));
        $this->assertTrue(is_callable($functions->getHasCallable()));
        $this->assertTrue(is_callable($functions->getDeleteCallable()));
        $this->assertTrue(is_callable($functions->getIteratorCallable()));
        $this->assertTrue(is_callable($functions->getAbortCallable()));
        $this->assertTrue(is_callable($functions->getCacheExpireCallable()));
        $this->assertTrue(is_callable($functions->getCacheLimiterCallable()));
        $this->assertTrue(is_callable($functions->getCreateIdCallable()));
        $this->assertTrue(is_callable($functions->getDecodeCallable()));
        $this->assertTrue(is_callable($functions->getDestroyCallable()));
        $this->assertTrue(is_callable($functions->getEncodeCallable()));
        $this->assertTrue(is_callable($functions->getGcCallable()));
        $this->assertTrue(is_callable($functions->getGetCookieParamsCallable()));
        $this->assertTrue(is_callable($functions->getIdCallable()));
        $this->assertTrue(is_callable($functions->getModuleNameCallable()));
        $this->assertTrue(is_callable($functions->getNameCallable()));
        $this->assertTrue(is_callable($functions->getRegenerateIdCallable()));
        $this->assertTrue(is_callable($functions->getRegisterShutdownCallable()));
        $this->assertTrue(is_callable($functions->getResetCallable()));
        $this->assertTrue(is_callable($functions->getSavePathCallable()));
        $this->assertTrue(is_callable($functions->getSetCookieParamsCallable()));
        $this->assertTrue(is_callable($functions->getSetSaveHandlerCallable()));
        $this->assertTrue(is_callable($functions->getStartCallable()));
        $this->assertTrue(is_callable($functions->getStatusCallable()));
        $this->assertTrue(is_callable($functions->getUnsetCallable()));
        $this->assertTrue(is_callable($functions->getWriteCloseCallable()));

        $key = 'some_key';
        $value = 'some_value';

        $prevSession = null;
        if (isset($_SESSION) === true) {
            $prevSession = $_SESSION;
        }
        try {
            $_SESSION = [];

            $this->assertFalse(call_user_func($functions->getHasCallable(), $key));
            call_user_func($functions->getPutCallable(), $key, $value);
            $this->assertTrue(call_user_func($functions->getHasCallable(), $key));
            $this->assertEquals($value, call_user_func($functions->getRetrieveCallable(), $key));
            call_user_func($functions->getDeleteCallable(), $key);
            $this->assertFalse(call_user_func($functions->getHasCallable(), $key));
            $sessionData = iterator_to_array(call_user_func($functions->getIteratorCallable()));
            $this->assertEquals([], $sessionData);
        } finally {
            if (isset($prevSession) === true) {
                $_SESSION = $prevSession;
                unset($prevSession);
            }
        }
    }

    /**
     * Test session calls wrapper functions.
     */
    public function testSessionCallsWrapper(): void
    {
        /** @var Mock $functions */
        $functions = Mockery::mock(SessionFunctionsInterface::class);

        $testKey = 'whatever';
        $testValue = 'value';
        $functions->shouldReceive('getPutCallable')->once()->withNoArgs()->andReturn(
            function ($key, $value) use ($testKey, $testValue) {
                $this->assertSame($testKey, $key);
                $this->assertSame($testValue, $value);
            }
        );
        $functions->shouldReceive('getRetrieveCallable')->once()->withNoArgs()->andReturn(
            function ($key) use ($testKey, $testValue) {
                $this->assertSame($testKey, $key);

                return $testValue;
            }
        );
        $functions->shouldReceive('getHasCallable')->once()->withNoArgs()->andReturn(
            function ($key) use ($testKey, $testValue) {
                $this->assertSame($testKey, $key);

                return true;
            }
        );
        $functions->shouldReceive('getDeleteCallable')->once()->withNoArgs()->andReturn(
            function ($key) use ($testKey, $testValue) {
                $this->assertSame($testKey, $key);
            }
        );
        $functions->shouldReceive('getIteratorCallable')->once()->withNoArgs()->andReturn(
            function () use ($testKey, $testValue) {
                return new ArrayIterator([$testKey => $testValue]);
            }
        );

        /** @var SessionFunctionsInterface $functions */

        $session = new Session($functions);

        $session[$testKey] = $testValue;

        $this->assertSame($testValue, $session[$testKey]);
        $this->assertTrue(isset($session[$testKey]));

        unset($session[$testKey]);

        $this->assertEquals([$testKey => $testValue], iterator_to_array($session->getIterator()));
    }
}
