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
     * Forbid public creation of instances of this class
     */
    private function __construct()
    {
    }
}
