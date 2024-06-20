<?php

declare(strict_types=1);

namespace App\Utility;

use \DateTime as DateTime;
use \DateTimeZone as DateTimeZone;

final class DateTimeManipulator
{

    public function __construct(
        private DateTime $dateTime,
        private ?DateTimeZone $defaultTimeZone = null,
        private ?DateTimeZone $utcTimeZone = null
    ) {
    }

    public static function getDefaultTimeZone(): DateTimeZone
    {
        return new DateTimeZone(date_default_timezone_get());
    }

    public static function getUtcTimeZone(): DateTimeZone
    {
        return new DateTimeZone('UTC');
    }

    private function bootstrapDefaultTimeZone(): void
    {
        if (!isset($this->defaultTimeZone)) {
            $this->defaultTimeZone = DateTimeManipulator::getDefaultTimeZone();
        }
    }

    private function bootstrapUtcTimeZone(): void
    {
        if (!isset($this->utcTimeZone)) {
            $this->utcTimeZone = DateTimeManipulator::getUtcTimeZone();
        }
    }

    public function setMidnight(bool $condition = true): self
    {
        if ($condition && $this->dateTime->format('Hisu') !== '000000000000') {
            $this->dateTime->setTime(0, 0, 0, 0);
        }
        return $this;
    }

    public function setStartOfYear(bool $condition = true): self
    {
        if ($condition && $this->dateTime->format('md') !== '0101') {
            $this->dateTime->setDate(
                (int)$this->dateTime->format('Y'),
                1,
                1
            );
        }
        return $this;
    }

    public function setStartOfMonth(bool $condition = true): self
    {
        if ($condition && $this->dateTime->format('d') !== '01') {
            $this->dateTime->setDate(
                (int)$this->dateTime->format('Y'),
                (int)$this->dateTime->format('m'),
                1
            );
        }
        return $this;
    }

    public function setStartOfWeek(bool $condition = true): self
    {
        if ($condition && $this->dateTime->format('N') !== '1') {
            $this->dateTime->setISODate(
                (int)$this->dateTime->format('Y'),
                (int)$this->dateTime->format('W'),
                1
            );
        }
        return $this;
    }

    /**
     * Sets the time of a DateTime object to one microsecond before midnight
     */
    public function setEndOfDay(bool $condition = true): self
    {
        if ($condition && $this->dateTime->format('Hisu') !== '235959999000') {
            $this->dateTime->setTime(23, 59, 59, 999000);
        }
        return $this;
    }

    public function setEndOfWeek(bool $condition = true): self
    {
        if ($condition && $this->dateTime->format('N') !== '7') {
            $this->dateTime->setISODate(
                (int)$this->dateTime->format('Y'),
                (int)$this->dateTime->format('W'),
                7
            );
        }
        return $this;
    }

    public function setEndOfMonth(bool $condition = true): self
    {
        if ($condition) {
            $this->dateTime->setDate(
                (int)$this->dateTime->format('Y'),
                (int)$this->dateTime->format('m'),
                (int)$this->dateTime->format('t')
            );
        }
        return $this;
    }

    public function setEndOfYear(bool $condition = true): self
    {
        if ($condition && $this->dateTime->format('md') !== '1231') {
            $this->dateTime->setDate(
                (int)$this->dateTime->format('Y'),
                12,
                31
            );
        }
        return $this;
    }


    /**
     * Set the year of a date to 1970
     */
    public function setMinYear(bool $condition = true): self
    {
        if ($condition && $this->dateTime->format('Y') !== '1970') {
            $this->dateTime->setDate(
                1970,
                (int)$this->dateTime->format('n'),
                (int)$this->dateTime->format('d')
            );
        }
        return $this;
    }

    public function setYear(int $year, bool $condition = true): self
    {
        if ($condition && $this->dateTime->format('Y') !== (string)$year) {
            $this->dateTime->setDate(
                $year,
                (int)$this->dateTime->format('n'),
                (int)$this->dateTime->format('d')
            );
        }
        return $this;
    }

    public function setMonth(int $month, bool $condition = true): self
    {
        if ($condition && $this->dateTime->format('n') !== (string)$month) {
            $this->dateTime->setDate(
                (int)$this->dateTime->format('Y'),
                $month,
                (int)$this->dateTime->format('d')
            );
        }
        return $this;
    }


    public function setDay(int $day, bool $condition = true): self
    {
        if ($condition && $this->dateTime->format('d') !== (string)$day) {
            $this->dateTime->setDate(
                (int)$this->dateTime->format('Y'),
                (int)$this->dateTime->format('n'),
                $day
            );
        }

        return $this;
    }

    public function setTimezone(string $timezone, bool $condition = true): self
    {
        if ($condition && $this->dateTime->format('e') !== $timezone) {
            $this->dateTime->setTimezone(new DateTimeZone($timezone));
        }
        return $this;
    }

    public function setUtc(bool $condition = true): self
    {
        if ($condition && $this->dateTime->format('e') !== 'UTC') {
            $this->bootstrapUtcTimeZone();
            $this->dateTime->setTimezone($this->utcTimeZone);
        }
        return $this;
    }

    /**
     * Convert the DateTime timezone to the system-default timezone
     */
    public function setDefaultTimezone(bool $condition = true): self
    {
        if ($condition && $this->dateTime->format('e') !== date_default_timezone_get()) {
            $this->bootstrapDefaultTimeZone();
            $this->dateTime->setTimezone($this->defaultTimeZone);
        }

        return $this;
    }

    public function modify(string $modifier, bool $condition = true): self
    {
        if ($condition) {
            $this->dateTime->modify($modifier);
        }
        return $this;
    }

    public function setStartOfHour(bool $condition = true): self
    {
        if ($condition && $this->dateTime->format('isu') !== '0000000000') {
            $this->dateTime->setTime((int)$this->dateTime->format('H'), 0, 0, 0);
        }
        return $this;
    }

    public function setEndOfHour(bool $condition = true): self
    {
        if ($condition && $this->dateTime->format('isu') !== '5959999000') {
            $this->dateTime->setTime((int)$this->dateTime->format('H'), 59, 59, 999000);
        }
        return $this;
    }

    public function get(): DateTime
    {
        return $this->dateTime;
    }
}
