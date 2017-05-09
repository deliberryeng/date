<?php

/**
 * This file is part of the deliberry/date package
 * For full copyright and license information, please view the
 *  LICENSE file that was distributed with this package.
 * (c) 2016 Deliberry.com
 */

namespace Deliberry;

use Deliberry\Date\DateException;
use DateTimeImmutable;
use SplStack;

/**
 * DateTime helper.
 *
 * Works with DateTimeImmutable instances.
 */
final class Date
{
    /**
     * Create DateTimeImmutable from formatted string.
     * Throws when errors or warnings in the process.
     *
     * @param string $formattedString Input string to convert.
     * @param string $format Format of the given string.
     *
     * @return DateTimeImmutable
     */
    public static function fromFormattedString(string $formattedString, string $format): DateTimeImmutable
    {
        $date = DateTimeImmutable::createFromFormat("!{$format}", $formattedString);

        $errors = DateTimeImmutable::getLastErrors();
        if (!$date instanceof DateTimeImmutable || !empty($errors['warnings'])) {
            throw DateException::invalidFormat($formattedString, $format);
        }

        return $date;
    }

    /**
     * Same as `fromFormattedString` but allows empty input, which is converted to current time.
     *
     * @param null|string $formattedString Input string to convert. If empty, uses current time.
     * @param string $format Format of the given string.
     *
     * @return DateTimeImmutable
     */
    public static function fromOptionalFormattedString(?string $formattedString, string $format): DateTimeImmutable
    {
        if (empty($formattedString)) {
            $formattedString = self::now()->format($format);
        }

        return self::fromFormattedString($formattedString, $format);
    }

    /**
     * Return current instant. Can be override by time freezes.
     *
     * @return DateTimeImmutable Current instant.
     */
    public static function now(): DateTimeImmutable
    {
        if (self::isTimeFrozen()) {
            return self::$frozenInstants->top();
        }

        return new DateTimeImmutable();
    }

    /**
     * Return current datetime with a modifier.
     *
     * @param string $modifier Modifier to apply to current datetime.
     *
     * @return DateTimeImmutable New instant with modifier applied.
     */
    public static function at(string $modifier): DateTimeImmutable
    {
        return self::now()->modify($modifier);
    }

    /**
     * Allows time freezing for test purposes.
     * Overrides current datetime.
     * Multiple calls stack one on top of the other.
     *
     * @internal Use this method only in your tests.
     *
     * @param DateTimeImmutable $instant Datetime to set as current.
     */
    public static function freezeTime(DateTimeImmutable $instant): void
    {
        if (!self::$frozenInstants) {
            self::$frozenInstants = new SplStack();
        }

        self::$frozenInstants->push($instant);
    }

    /**
     * Removes current frozen instant.
     * If stack is left empty, then time flows as usual again.
     * Throws if the stack is already empty.
     *
     * @internal Use this method only in your tests.
     *
     * @return DateTimeImmutable Previous frozen instant.
     */
    public static function unfreezeTime(): DateTimeImmutable
    {
        if (!self::isTimeFrozen()) {
            throw new \LogicException(
                'Can’t pop frozen time from empty stack. Maybe one too many calls to `unfreezeTime()`?'
            );
        }

        return self::$frozenInstants->pop();
    }

    /**
     * Check freezeTime time status.
     *
     * @internal Use this method only in your tests.
     *
     * @return boolean Whether time is currently frozen.
     */
    public static function isTimeFrozen(): bool
    {
        return self::$frozenInstants && !self::$frozenInstants->isEmpty();
    }

    /**
     * Forbid public creation of instances of this class
     */
    private function __construct()
    {
    }

    /** @var SplStack|DateTimeImmutable[]|null */
    private static $frozenInstants;
}
