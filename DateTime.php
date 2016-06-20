<?php
/**
 * @author Sébastien Roux
 */
namespace Zac2\Common;

class DateTime extends \DateTime
{

    const FORMAT_FULL            = 'Y-m-d H:i:s.µ';
    const FORMAT_MYSQL_DATE      = 'MYSQL:D';
    const FORMAT_MYSQL_TIME      = 'MYSQL:T';
    const FORMAT_MYSQL_DATETIME  = 'MYSQL:DT';
    const FORMAT_FR_DATE_LONG    = 'FR:DL';
    const FORMAT_FR_DATE_COURT   = 'FR:DC';
    const FORMAT_HUMAN_DATE      = 'human date';

    const PATTERN_DAY      = '([0]{0,1}[123456789]|[12][0-9]|3[01])';
    const PATTERN_MONTH    = '([0]{0,1}[123456789]|1[012])';
    const PATTERN_YEAR     = '(([0-9]{2}){0,1}[0-9]{2})';
    const PATTERN_HOUR     = '([0-2]{0,1}[0-9])';
    const PATTERN_MIN_SEC  = '([0-9]{0,1}[0-9])';
    const PATTERN_MICROSEC = '(\.[0-9]+)';

    /** @var  array */
    protected static $frenchTranslation = array(
        'Monday'	=> 'lundi'      , 'Mon'	=> 'lun',
        'Tuesday'	=> 'mardi'      , 'Tue'	=> 'mar',
        'Wednesday'	=> 'mercredi'   , 'Wed'	=> 'mer',
        'Thursday'	=> 'jeudi'      , 'Thu'	=> 'jeu',
        'Friday'	=> 'vendredi'   , 'Fri'	=> 'ven',
        'Saturday'	=> 'samedi'     , 'Sat'	=> 'sam',
        'Sunday'	=> 'dimanche'   , 'Sun'	=> 'dim',

        'January'	=> 'janvier'    , 'Jan'	=> 'jan',
        'February'	=> 'février'    , 'Feb'	=> 'fév',
        'March'		=> 'mars'       , 'Mar'	=> 'mar',
        'April'		=> 'avril'      , 'Apr'	=> 'avr',
        'May'		=> 'mai'        ,
        'June'		=> 'juin'       , 'Jun'	=> 'jun',
        'July'		=> 'juillet'    , 'Jul'	=> 'jul',
        'August'	=> 'août'       , 'Aug'	=> 'aoû',
        'September'	=> 'septembre'  , 'Sep'	=> 'sep',
        'October'	=> 'octobre'    , 'Oct'	=> 'oct',
        'November'	=> 'novembre'   , 'Nov'	=> 'nov',
        'December'	=> 'décembre'   , 'Dec'	=> 'déc',

        'today'     => "Aujourd'hui",
        'yesterday' => 'hier'       ,
        'tomorrow'  => 'demain'     ,
    );

    /** @var  string */
    protected static $defaultFormat;
    /** @var  float */
    protected $microSeconds = 0;

    /**
     * @param  string|null $interval
     * @param  string|null $modify
     * @return static
     */
    public static function now($interval = null, $modify = null)
    {
        return new static(null, $interval, $modify);
    }

    /**
     * @param  string $dateString
     * @return string
     */
    public static function translate($dateString)
    {
        return str_replace(
            array_keys  (static::$frenchTranslation),
            array_values(static::$frenchTranslation),
            $dateString
        );
    }

    /**
     * @param  string $format
     * @throws \Exception
     */
    public static function setDefaultFormat($format)
    {
        if (!is_string($format)) {
            throw new \Exception("Bad defaultformat format");
        }

        static::$defaultFormat = $format;
    }

    /**
     * @return string
     */
    public static function getDefaultFormat()
    {
        return static::$defaultFormat;
    }

    /**
     * @param  string $operator operator for comparison : min or max
     * @param  array  $argLst
     * @return static
     * @throws \Exception
     */
    protected static function compareDateTime($operator, array $argLst)
    {
        $dateTime = null;

        if (count($argLst) == 1) {
            $arg = current($argLst);

            if (is_array($arg)) {
                $argLst = $arg;
            } else {
                return new static($arg);
            }
        } elseif (count($argLst) < 1) {
            throw new \Exception("Arguments expected");
        }

        foreach ($argLst as $dt) {
            $dt = new static($dt);

            if (!$dateTime) {
                $dateTime = $dt;
            } elseif ($operator == 'min' && ($dt < $dateTime)) {
                $dateTime = $dt;
            } elseif ($operator == 'max' && ($dt > $dateTime)) {
                $dateTime = $dt;
            }
        }

        return $dateTime;
    }

    /**
     * @return static
     */
    public static function minDateTime()
    {
        return static::compareDateTime('min', func_get_args());
    }

    /**
     * @return static
     */
    public static function maxDateTime()
    {
        return static::compareDateTime('max', func_get_args());
    }

    /**
     * @param  static|string|null $dateTimeStart
     * @param  static|string|null $dateTimeEnd
     * @return float
     */
    public static function getDuration($dateTimeStart, $dateTimeEnd = null)
    {
        $dateTimeStart = new static($dateTimeStart);
        $dateTimeEnd   = new static($dateTimeEnd);

        $timeStart = floatval($dateTimeStart->format('U') + $dateTimeStart->getMicroSeconds());
        $timeEnd   = floatval($dateTimeEnd  ->format('U') + $dateTimeEnd  ->getMicroSeconds());

        return $timeEnd - $timeStart;
    }

    /**
     * @param  static|string|null $dateTime
     * @param  string $format
     * @return string
     */
    public static function convertToFormat($dateTime, $format)
    {
        $dateTime = new static($dateTime);
        return $dateTime->format($format);
    }

    /**
     * @param  static|DateTime|string|int|float|null $dateTime
     * @param  string|null $interval
     * @param  string|null $modify
     */
    public function __construct($dateTime = null, $interval = null, $modify = null)
    {
        parent::__construct();

        $this->setDefaultFormat(static::FORMAT_FR_DATE_COURT);
        $this->setDateTime($dateTime);

        if ($interval) {
            if (substr($interval, 0, 1) == '-') {
                $this->sub(new \DateInterval(substr($interval, 1)));
            } else {
                $this->add(new \DateInterval($interval));
            }
        }

        if ($modify) { $this->modify($modify); }
    }

    /**
     * @param  static|DateTime|string|int|float|null $dateTime
     * @throws \Exception
     */
    public function setDateTime($dateTime = null)
    {
        if (is_null($dateTime)) {
            parent::__construct();
            $this->setMicroSeconds();
            return;
        }

        if ($dateTime instanceof static) {
            $dateTime = $dateTime->format(static::FORMAT_FULL);
        }

        if ($dateTime instanceof DateTime) {
            $dateTime = $dateTime->format('c');
        }

        if (is_scalar($dateTime)) {
            $dateTime = trim($dateTime);

            //----- Microseconds -----
            if (preg_match ('/' . static::PATTERN_MICROSEC . '$/', $dateTime, $rst)) {
                $this->setMicroSeconds($rst[1]);
                $dateTime = preg_replace('/' . static::PATTERN_MICROSEC . '$/', '', $dateTime);
            }

            //----- Format [J]J(/|-)[M]M(/|-)[AA]AA[ [H]H:[M]M[:[S]S] -----
            if (preg_match ('/^' . static::PATTERN_DAY . '[\/-]' . static::PATTERN_MONTH . '[\/-]' . static::PATTERN_YEAR . '(\s+' . static::PATTERN_HOUR . ':' . static::PATTERN_MIN_SEC . '(:' . static::PATTERN_MIN_SEC . '){0,1}){0,1}$/', $dateTime, $rst)) {
                $this->setDate(($rst[3] < 100 ? substr(date('Y'), 0, 2) . $rst[3] : $rst[3]), $rst[2], $rst[1]);
                $this->setTime((isset($rst[6]) ? $rst[6] : 0), (isset($rst[7]) ? $rst[7] : 0), (isset($rst[9]) ? $rst[9] : 0));
                return;
            }

            //----- Format AAAAMMJJHHMMSS-----
            if (preg_match ('/^' . str_replace('{0,1}', '', static::PATTERN_YEAR . static::PATTERN_MONTH . static::PATTERN_DAY . static::PATTERN_HOUR . static::PATTERN_MIN_SEC . static::PATTERN_MIN_SEC) . '$/', $dateTime, $rst)) {
                $this->setDate($rst[1], $rst[3], $rst[4]);
                $this->setTime($rst[5], $rst[6], $rst[7]);
                return;
            }

            //----- Format AAAAMMJJ -----
            if (preg_match ('/^' . str_replace('{0,1}', '', static::PATTERN_YEAR . static::PATTERN_MONTH . static::PATTERN_DAY) . '$/', $dateTime, $rst)) {
                $this->setDate($rst[1], $rst[3], $rst[4]);
                $this->setTime(0, 0, 0);
                return;
            }

            //----- Format in seconds -----
            if (preg_match ('/^[0-9]+$/', $dateTime, $rst)) {
                $this->setDate(date('Y', $dateTime), date('m', $dateTime), date('d', $dateTime));
                $this->setTime(date('H', $dateTime), date('i', $dateTime), date('s', $dateTime));
                return;
            }

            //----- default -----
            parent::__construct($dateTime);
            return;
        }

        throw new \Exception('Bad datetime format "' . $dateTime . '"');
    }

    /**
     * @param  float|null $microSeconds
     * @throws \Exception
     */
    public function setMicroSeconds($microSeconds = null)
    {
        if ($microSeconds === null) {
            $m = explode(' ', microtime());
            $microSeconds = $m[0];
        }

        if (!is_numeric($microSeconds)) {
            throw new \Exception("Bad microseconds format");
        }

        $this->microSeconds = floatval($microSeconds);
    }

    /**
     * @param  bool $integerFormat
     * @return float|int
     */
    public function getMicroSeconds($integerFormat = false)
    {
        if ($integerFormat) {
            return intval(substr($this->microSeconds, 2));
        }

        return $this->microSeconds;
    }

    /**
     * @return float
     */
    public function getDecimalHour()
    {
        $hours   = intval($this->format('G')) * 3600;
        $minutes = intval($this->format('i')) * 60;
        $seconds = intval($this->format('s'));

        return ($hours + $minutes + $seconds) / 3600;
    }

    /**
     * @param  static|string|null $dateTime
     * @return float
     */
    public function getDurationWith($dateTime = null)
    {
        $dateTime = new static($dateTime);

        return static::getDuration($this, $dateTime);
    }

    /**
     * @return bool
     */
    public function isHoliday()
    {
        return Calendar::isHoliday($this);
    }

    /**
     * @return bool
     */
    public function isWorkingDay()
    {
        return Calendar::isWorkingDay($this);
    }

    /**
     * @param  string $str
     * @return array|bool
     */
    public static function pregDate($str)
    {
        if (preg_match ('/^' . static::PATTERN_YEAR . '[-]' . static::PATTERN_MONTH . '[-]' . static::PATTERN_DAY . '$/', $str, $matches)) {
            return array(
                'day'   => $matches[3],
                'month' => $matches[2],
                'year'  => $matches[1],
            );
        }

        if (preg_match ('/^' . static::PATTERN_DAY . '[\/-]' . static::PATTERN_MONTH . '[\/-]' . static::PATTERN_YEAR . '$/', $str, $matches)) {
            return array(
                'day'   => $matches[1],
                'month' => $matches[2],
                'year'  => $matches[3],
            );
        }

        return false;
    }

    /**
     * @param  string $str
     * @return array|bool
     */
    public static function pregTime($str)
    {
        if (preg_match ('/^' . static::PATTERN_HOUR . ':' . static::PATTERN_MIN_SEC . '(:' . static::PATTERN_MIN_SEC . '){0,1}(\.' . static::PATTERN_MICROSEC . '){0,1}$/', $str, $matches)) {
            return array(
                'hour'    => $matches[1],
                'minute'  => $matches[2],
                'seconde' => $matches[4],
                'microsd' => $matches[6],
            );
        }

        return false;
    }

    /**
     *
     */
    public function modifyToBeWorkingDay()
    {
        if ($this->isWorkingDay()) { return; }

        $this->setDateTime(Calendar::nextWorkingDay($this));
    }

    /**
     * @return string
     */
    public function getHumanDate()
    {
        if ($this->format('Ymd') == static::now()->format('Ymd')) {
            return 'today';
        }
        if ($this->format('Ymd') == static::now(null, '-1 day')->format('Ymd')) {
            return 'yesterday';
        }
        if ($this->format('Ymd') == static::now(null, '+1 day')->format('Ymd')) {
            return 'tomorrow';
        }

        return $this->format(static::getDefaultFormat());
    }

    /**
     * @param  string $format
     * @return string|int
     */
    public function format($format)
    {
        if ($format == static::FORMAT_HUMAN_DATE) {
            $format = $this->getHumanDate();
        }

        $convFormat = array(
            static::FORMAT_MYSQL_DATETIME => 'Y-m-d H:i:s',
            static::FORMAT_MYSQL_DATE     => 'Y-m-d',
            static::FORMAT_MYSQL_TIME     => 'H:i:s',
            static::FORMAT_FR_DATE_LONG   => 'l j F Y',
            static::FORMAT_FR_DATE_COURT  => 'd/m/Y',
        );

        $format = str_replace(array_keys($convFormat), array_values($convFormat), $format);
        $format = str_replace('µ', $this->getMicroSeconds(true), $format);

        return static::translate(parent::format($format));
    }

    /**
     * @return string
     */
    public function toArray()
    {
        return $this->__toString();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->format(static::$defaultFormat);
    }

}

DateTime::setDefaultFormat(DateTime::FORMAT_FR_DATE_COURT);