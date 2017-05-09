<?php

/**
 * This file is part of the deliberry/date package
 * For full copyright and license information, please view the
 *  LICENSE file that was distributed with this package.
 * (c) 2016 Deliberry.com
 */

use Deliberry\Date;
use PHPUnit\Framework\TestCase;

final class DateTest extends TestCase
{
    const DELTA_EQUAL = 0.001;

    public function test_creation_from_formatted_string()
    {
        $expected = (new DateTimeImmutable())->setDate(2017, 11, 28)->setTime(14, 5, 10);

        $result = Date::fromFormattedString('2017-11-28 14:05:10', 'Y-m-d H:i:s');

        self::assertDatesAreNear($expected, $result);
    }

    public function test_creation_from_incomplete_format()
    {
        $expected = (new DateTimeImmutable())->setDate(2017, 11, 1)->setTime(0, 0, 0);

        $result = Date::fromFormattedString('2017-11', 'Y-m');

        self::assertDatesAreNear($expected, $result);
    }

    public function test_creation_failed_from_empty_string()
    {
        self::expectException(Deliberry\Date\DateException::class);

        Date::fromFormattedString('', 'U.u');
    }

    public function test_creation_failed_from_wrong_formatted_string()
    {
        self::expectException(Deliberry\Date\DateException::class);

        Date::fromFormattedString('2017-01-08 04:05', 'Y-m-d H:i:s');
    }

    public function test_creation_failed_from_well_formatted_string_but_invalid_date()
    {
        self::expectException(Deliberry\Date\DateException::class);

        Date::fromFormattedString('2017-01-35', 'Y-m-d');
    }

    public function test_creation_from_optional_string_with_empty_returns_current()
    {
        $expected = DateTimeImmutable::createFromFormat('U', time());

        $instant = Date::fromOptionalFormattedString('', 'Y-m-d H:i:s');

        self::assertDatesAreNear($expected, $instant);
    }

    public function test_creation_from_optional_string_with_null_returns_current()
    {
        $expected = new DateTimeImmutable('today 00:00');

        $instant = Date::fromOptionalFormattedString(null, 'Y-m-d');

        self::assertDatesAreNear($expected, $instant);
    }

    public function test_now_returns_current_instant()
    {
        $expected = new DateTimeImmutable();

        $instant = Date::now();

        self::assertDatesAreNear($expected, $instant);
    }

    public function test_at_allows_modification()
    {
        $expected = new DateTimeImmutable('8 minutes ago');

        $instant = Date::at('8 minutes ago');

        self::assertDatesAreNear($expected, $instant);
    }

    public function test_at_allows_fixed_modification()
    {
        $expected = new DateTimeImmutable('tomorrow 08:05');

        $instant = Date::at('tomorrow 08:05');

        self::assertDatesAreNear($expected, $instant);
    }

    public function test_dates_are_near_assertion_fails()
    {
        self::expectException(PHPUnit\Framework\ExpectationFailedException::class);
        self::assertDatesAreNear(new DateTimeImmutable(), new DateTimeImmutable('+1 hour'));
    }

    private function assertDatesAreNear(DateTimeImmutable $lhs, DateTimeImmutable $rhs, float $delta = self::DELTA_EQUAL)
    {
        self::assertThat(
            abs($lhs->format('U.u') - $rhs->format('U.u')),
            self::lessThan($delta),
            'Dates should be equal'
        );
    }
}
