<?php
/**
 * Parser based on https://github.com/yalesov/php-cron-expr-parser
 */

namespace Azurre\Component\Cron;

/**
 * Class Expression
 */
class Expression
{
    const MINUTE = 0;
    const HOUR = 1;
    const DAY_OF_MONTH = 2;
    const MONTH = 3;
    const DAY_OF_WEEk = 4;

    const MONDAY = 1;
    const TUESDAY = 2;
    const WEDNESDAY = 3;
    const THURSDAY = 4;
    const FRIDAY = 5;
    const SATURDAY = 6;
    const SUNDAY = 7;

    const DEFAULT_EXPRESSION = ['*', '*', '*', '*', '*'];

    protected $expression = self::DEFAULT_EXPRESSION;

    /**
     * @return $this
     */
    public static function create()
    {
        return new static;
    }

    /**
     * @param string $expression
     * @return $this
     */
    public static function parse($expression)
    {
        $expr = preg_split('/\s+/', $expression);
        $count = \count($expr);
        if ($count < 5) {
            throw new \InvalidArgumentException('Cannot parse cron expression');
        }
        $instance = static::create();
        $instance->expression = array_slice($expr, 0, 5);
        return $instance;
    }

    /**
     * @param string|number|null $time
     * @return bool
     */
    public function match($time = null)
    {
        return static::matchTime($time === null ? time() : $time, (string)$this);
    }

    /**
     * @param int $minute 0 - 59
     * @return $this
     */
    public function hourly($minute = 0)
    {
        return $this->reset()->setMinute($minute);
    }

    /**
     * @param int|string|null $timeOrHour
     * @param int|null $minutes
     * @return $this
     */
    public function daily($timeOrHour = null, $minutes = null)
    {
        return $this->reset()->at($timeOrHour, $minutes);
    }

    /**
     * @param int|string $dayOfWeek
     */
    public function weekly($dayOfWeek = self::MONDAY)
    {
        return $this->reset()->setDayOfWeek($dayOfWeek)->at(0, 0);
    }

    /**
     * @param int $dayOfMonth 1 - 31
     * @return $this
     */
    public function monthly($dayOfMonth = 1)
    {
        return $this->reset()->setDayOfMonth($dayOfMonth)->at(0, 0);
    }

    /**
     * @param int|string|null $timeOrHour
     * @param int|null $minutes
     * @return $this
     */
    public function at($timeOrHour = null, $minutes = null)
    {
        if (is_numeric($timeOrHour) && is_numeric($minutes)) {
            $hour = (int)$timeOrHour;
            $minutes = (int)$minutes;
        } else if (is_string($timeOrHour) && strpos($timeOrHour, ':') !== false && $minutes === null) {
            list($hour, $minutes) = explode(':', $timeOrHour, 2);
            $hour = (int)$hour;
            $minutes = (int)$minutes;
        } else {
            $hour = $minutes = 0;
        }
        return $this->setHour($hour)->setMinute($minutes);
    }

    /**
     * @param string|int $minute
     * @return $this
     */
    public function setMinute($minute)
    {
        $this->expression[static::MINUTE] = $minute;
        return $this;
    }

    /**
     * @param string|int $hour
     * @return $this
     */
    public function setHour($hour)
    {
        $this->expression[static::HOUR] = $hour;
        return $this;
    }

    /**
     * @param int $dayOfMonth
     * @return $this
     */
    public function setDayOfMonth($dayOfMonth)
    {
        $this->expression[static::DAY_OF_MONTH] = $dayOfMonth;
        return $this;
    }

    /**
     * @param string|int $month
     * @return $this
     */
    public function setMonth($month)
    {
        $monthNumeric = static::exprToNumeric($month);
        $this->expression[static::MONTH] = $monthNumeric === false ? $month: $monthNumeric;
        return $this;
    }

    /**
     * @param string|int $dayOfWeek
     * @return $this
     */
    public function setDayOfWeek($dayOfWeek)
    {
        $dayOfWeekNumeric = static::exprToNumeric($dayOfWeek);
        $this->expression[static::DAY_OF_WEEk] = $dayOfWeekNumeric === false ? $dayOfWeek: $dayOfWeekNumeric;
        return $this;
    }

    /**
     * @return $this
     */
    public function reset()
    {
        $this->expression = self::DEFAULT_EXPRESSION;
        return $this;
    }

    /**
     * determine whether a given time falls within the given cron expr
     *
     * @param string|numeric $time
     *    timestamp or strtotime()-compatible string
     * @param string $expr
     *    any valid cron expression, in addition supporting:
     *    range: '0-5'
     *    range + interval: '10-59/5'
     *    comma-separated combinations of these: '1,4,7,10-20'
     *    English months: 'january'
     *    English months (abbreviated to three letters): 'jan'
     *    English weekdays: 'monday'
     *    English weekdays (abbreviated to three letters): 'mon'
     *    These text counterparts can be used in all places where their
     *      numerical counterparts are allowed, e.g. 'jan-jun/2'
     *    A full example:
     *      '0-5,10-59/5 * 2-10,15-25 january-june/2 mon-fri' -
     *      every minute between minute 0-5 + every 5th min between 10-59
     *      every hour
     *      every day between day 2-10 and day 15-25
     *      every 2nd month between January-June
     *      Monday-Friday
     * @return bool
     */
    public static function matchTime($time, $expr)
    {
        $cronExpr = preg_split('/\s+/', $expr, -1, PREG_SPLIT_NO_EMPTY);
        if (count($cronExpr) !== 5) {
            throw new \InvalidArgumentException(
                sprintf(
                    'cron expression should have exactly 5 arguments, "%s" given',
                    $expr
                )
            );
        }
        if (is_string($time)) {
            $time = strtotime($time);
        }
        $date = getdate($time);
        return self::matchTimeComponent($cronExpr[0], $date['minutes']) && self::matchTimeComponent(
                $cronExpr[1],
                $date['hours']
            ) && self::matchTimeComponent($cronExpr[2], $date['mday']) && self::matchTimeComponent(
                $cronExpr[3],
                $date['mon']
            ) && self::matchTimeComponent($cronExpr[4], $date['wday']);
    }

    /**
     * match a cron expression component to a given corresponding date/time
     *
     * In the expression, * * * * *, each component
     *    *[1] *[2] *[3] *[4] *[5]
     * will correspond to a getdate() component
     * 1. $date['minutes']
     * 2. $date['hours']
     * 3. $date['mday']
     * 4. $date['mon']
     * 5. $date['wday']
     *
     * @param string $expr
     * @param numeric $num
     * @return bool
     * @see self::exprToNumeric() for additional valid string values
     *
     */
    public static function matchTimeComponent($expr, $num)
    {
        //handle all match
        if ($expr === '*') {
            return true;
        }
        //handle multiple options
        if (strpos($expr, ',') !== false) {
            $args = explode(',', $expr);
            foreach ($args as $arg) {
                if (self::matchTimeComponent($arg, $num)) {
                    return true;
                }
            }
            return false;
        }
        //handle modulus
        if (strpos($expr, '/') !== false) {
            $arg = explode('/', $expr);
            if (count($arg) !== 2) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'invalid cron expression component: ' . 'expecting match/modulus, "%s" given',
                        $expr
                    )
                );
            }
            if (!is_numeric($arg[1])) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'invalid cron expression component: ' . 'expecting numeric modulus, "%s" given',
                        $expr
                    )
                );
            }
            $expr = $arg[0];
            $mod = $arg[1];
        } else {
            $mod = 1;
        }
        //handle all match by modulus
        if ($expr === '*') {
            $from = 0;
            $to = 60;
        } //handle range
        elseif (strpos($expr, '-') !== false) {
            $arg = explode('-', $expr);
            if (count($arg) !== 2) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'invalid cron expression component: ' . 'expecting from-to structure, "%s" given',
                        $expr
                    )
                );
            }
            $from = self::exprToNumeric($arg[0]);
            $to = self::exprToNumeric($arg[1]);
        } //handle regular token
        else {
            $from = self::exprToNumeric($expr);
            $to = $from;
        }
        if ($from === false || $to === false) {
            throw new \InvalidArgumentException(
                sprintf(
                    'invalid cron expression component: ' . 'expecting numeric or valid string, "%s" given',
                    $expr
                )
            );
        }
        return ($num >= $from) && ($num <= $to) && ($num % $mod === 0);
    }

    /**
     * parse a string month / weekday expression to its numeric equivalent
     *
     * @param string|numeric $value
     *    accepts, case insensitive,
     *    - Jan - Dec
     *    - Sun - Sat
     *    - (or their long forms - only the first three letters important)
     * @return int|false
     */
    public static function exprToNumeric($value)
    {
        static $data = [
            'jan' => 1,
            'feb' => 2,
            'mar' => 3,
            'apr' => 4,
            'may' => 5,
            'jun' => 6,
            'jul' => 7,
            'aug' => 8,
            'sep' => 9,
            'oct' => 10,
            'nov' => 11,
            'dec' => 12,
            'sun' => 0,
            'mon' => 1,
            'tue' => 2,
            'wed' => 3,
            'thu' => 4,
            'fri' => 5,
            'sat' => 6,
        ];
        if (is_numeric($value)) {
            // allow all numerics values, this change fix the bug for minutes range like 0-59 or hour range like 0-20
            return $value;
        }
        if (is_string($value)) {
            $value = strtolower(substr($value, 0, 3));
            if (isset($data[$value])) {
                return $data[$value];
            }
        }
        return false;
    }

    public function __toString()
    {
        return implode(' ', $this->expression);
    }
}
