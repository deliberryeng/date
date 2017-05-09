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

    public function test_freezes_time_for_now()
    {
        $expected = new DateTimeImmutable('yesterday 06:05');
        Date::freezeTime($expected);

        $instant = Date::now();

        self::assertDatesAreNear($expected, $instant);
    }
    public function test_freezes_time_for_at()
    {
        $expected = Date::at('today 06:15');
        $frozenInstant = Date::at('yesterday 08:05');
        Date::freezeTime($frozenInstant);

        $instant = Date::at('tomorrow 06:15');

        self::assertDatesAreNear($expected, $instant);
    }

    public function test_unfreezing_goes_back_to_normal()
    {
        $frozenInstant = new DateTimeImmutable('tomorrow 18:05');
        Date::freezeTime($frozenInstant);

        Date::unfreezeTime();

        $expected = new DateTimeImmutable();
        $instant = Date::now();

        self::assertDatesAreNear($expected, $instant);
    }

    public function test_unfreeze_returns_last_frozen_time()
    {
        $expected = new DateTimeImmutable('today 18:05');
        Date::freezeTime($expected);

        $instant = Date::unfreezeTime();

        self::assertDatesAreNear($expected, $instant);
    }

    public function test_allows_multiple_freezing()
    {
        Date::freezeTime(new DateTimeImmutable('tomorrow 18:05'));

        $expected = new DateTimeImmutable('today 06:15');
        Date::freezeTime($expected);

        $instant = Date::now();

        self::assertDatesAreNear($expected, $instant);
    }

    public function test_unfreeze_goes_back_to_previous_frozen_instant()
    {
        $expected = new DateTimeImmutable('tomorrow 18:05');
        Date::freezeTime($expected);

        Date::freezeTime(new DateTimeImmutable('today 06:15'));

        Date::unfreezeTime();

        $instant = Date::now();

        self::assertDatesAreNear($expected, $instant);
    }

    public function test_time_is_initially_not_frozen()
    {
        self::assertFalse(Date::isTimeFrozen());
    }

    public function test_time_is_frozen_after_freezing()
    {
        Date::freezeTime(new DateTimeImmutable());

        self::assertTrue(Date::isTimeFrozen());
    }

    public function test_time_is_not_frozen_after_calling_unfreeze()
    {
        Date::freezeTime(new DateTimeImmutable());
        Date::unfreezeTime();

        self::assertFalse(Date::isTimeFrozen());
    }

    public function test_time_is_frozen_after_multiple_freezing()
    {
        Date::freezeTime(new DateTimeImmutable());
        Date::freezeTime(new DateTimeImmutable('tomorrow'));

        self::assertTrue(Date::isTimeFrozen());
    }

    public function test_time_is_still_frozen_after_single_unfreeze_on_multiple_freezing()
    {
        Date::freezeTime(new DateTimeImmutable());
        Date::freezeTime(new DateTimeImmutable('tomorrow'));
        Date::unfreezeTime();

        self::assertTrue(Date::isTimeFrozen());
    }

    public function test_time_is_not_frozen_after_calling_all_unfreezes()
    {
        Date::freezeTime(new DateTimeImmutable());
        Date::freezeTime(new DateTimeImmutable('tomorrow'));
        Date::unfreezeTime();
        Date::unfreezeTime();

        self::assertFalse(Date::isTimeFrozen());
    }

    public function test_dates_are_near_assertion_fails()
    {
        self::expectException(PHPUnit\Framework\ExpectationFailedException::class);
        self::assertDatesAreNear(new DateTimeImmutable(), new DateTimeImmutable('+1 hour'));
    }

    public function tearDown()
    {
        parent::tearDown();

        while (Date::isTimeFrozen()) {
            Date::unfreezeTime();
        }
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
