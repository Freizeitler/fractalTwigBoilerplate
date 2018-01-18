<?php

namespace webartig\common\releasetools\services;

use webartig\common\releasetools\helper\LoggerColor;

class Logger
{
    protected $color = LoggerColor::WHITE;

    /**
     * @param string $msg A message
     * @param LoggerColor $color
     */
    public function write($msg, $color = null)
    {
        $color = $color ?: $this->color;
        print "\033[" . $color . "m" . $msg . "\033[37m";
    }

    /**
     * @param string $msg A message
     * @param LoggerColor $color
     */
    public function writeLn($msg, $color = null)
    {
        $this->write($msg . "\n", $color);
    }

    /**
     * @param LoggerColor $color
     */
    public function setColor(LoggerColor $color)
    {
        $this->color = $color;
    }

    /**
     * @param $msg
     * @param null $color
     * @param bool $nl new line
     */
    public static function text($msg, $color = null, $nl = true)
    {
        $logger = new Logger();
        $nl ? $logger->writeLn($msg, $color) : $logger->write($msg, $color);
    }

    /**
     * @param $msg
     * @param bool $nl new line
     */
    public static function info($msg, $nl = true)
    {
        self::text($msg, LoggerColor::WHITE, $nl);
    }

    /**
     * @param $msg
     * @param bool $nl new line
     */
    public static function success($msg, $nl = true)
    {
        self::text($msg, LoggerColor::GREEN, $nl);
    }

    /**
     * @param $msg
     * @param bool $nl new line
     */
    public static function warn($msg, $nl = true)
    {
        self::text($msg, LoggerColor::YELLOW, $nl);
    }

    /**
     * @param $msg
     * @param bool $nl new line
     */
    public static function error($msg, $nl = true)
    {
        self::text($msg, LoggerColor::RED, $nl);
    }

}