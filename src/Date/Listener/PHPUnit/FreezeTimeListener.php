<?php

namespace Deliberry\Date\Listener\PHPUnit;

use DateTimeImmutable;
use Deliberry\Date;
use PHPUnit\Framework\BaseTestListener;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;

/**
 * Allow PHPUnit tests to froze time of Deliberry\Date during execution through annotation.
 * By default this annotation is `@freezeTime`, but can be configured at construction.
 */
final class FreezeTimeListener extends BaseTestListener
{
    public function __construct($annotationName = 'freezeTime')
    {
        $this->annotationName = $annotationName;
    }

    public function startTest(Test $test)
    {
        $this->isTimeFrozen = false;
        if (!$test instanceof TestCase) {
            return;
        }

        $annotations = $this->getAnnotations($test, $this->annotationName);
        if (empty($annotations)) {
            return;
        }

        // Only applies first annotation
        foreach ($annotations as $timeModifier) {
            $this->isTimeFrozen = true;
            $this->freezeTime($timeModifier);
            break;
        }
    }

    public function endTest(Test $test, $time)
    {
        if ($this->isTimeFrozen) {
            $this->unfreezeTime();
        }
    }

    private function getAnnotations(TestCase $test, string $annotation)
    {
        $annotations = $test->getAnnotations();
        foreach ([ 'method', 'class' ] as $context) {
            if (isset($annotations[$context][$annotation])) {
                return $annotations[$context][$annotation];
            }
        }

        return false;
    }

    private function freezeTime(string $modifier)
    {
        Date::freezeTime(new DateTimeImmutable($modifier));
    }

    private function unfreezeTime()
    {
        Date::unfreezeTime();
    }

    private $annotationName;
    private $isTimeFrozen = false;
}
