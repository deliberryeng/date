<?php

/**
 * This file is part of the deliberry/date package
 * For full copyright and license information, please view the
 *  LICENSE file that was distributed with this package.
 * (c) 2016 Deliberry.com
 */

use Deliberry\Date;
use PHPUnit\Framework\TestCase;

final class FreezeTimeListenerTest extends TestCase
{
    /**
     * @freezeTime
     */
    public function test_freezes_time()
    {
        $expected = Date::now();
        $current = Date::now();

        self::assertEquals($expected, $current);
    }

    /**
     * @freezeTime yesterday 08:00
     */
    public function test_freezes_time_to_given_modifier()
    {
        $expected = new DateTimeImmutable('yesterday 08:00');
        $current = Date::now();

        self::assertEquals($expected, $current);
    }
}
