<?php

/**
 * This file is part of the deliberry/date package
 * For full copyright and license information, please view the
 *  LICENSE file that was distributed with this package.
 * (c) 2016 Deliberry.com
 */

namespace Deliberry\Date;

use InvalidArgumentException;

final class DateException extends InvalidArgumentException
{
    public static function invalidFormat(string $formattedString, string $format)
    {
        return new self(sprintf(
            'Date must comply with format "%s" but "%s" given.',
            $format,
            $formattedString
        ));
    }
}
