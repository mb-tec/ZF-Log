<?php

namespace MBtecZfLog\Service;

/**
 * Class        StaticLogger
 * @package     MBtecZfLog\Service
 * @author      Matthias Büsing <info@mb-tec.eu>
 * @copyright   2016 Matthias Büsing
 * @license     GNU General Public License
 * @link        http://mb-tec.eu
 */
class StaticLogger
{
    protected static $_oLogservice = null;

    /**
     * @param      $iLevel
     * @param      $sMsg
     * @param null $sFile
     *
     * @return mixed
     */
    public static function log($iLevel, $sMsg, $sFile = null)
    {
        return self::$_oLogservice->log($iLevel, $sMsg, $sFile);
    }

    /**
     * @param        $sMsg
     * @param string $sFile
     * @return mixed
     */
    public static function emerg($sMsg, $sFile = null)
    {
        return self::$_oLogservice->emerg($sMsg, $sFile);
    }

    /**
     * @param        $sMsg
     * @param string $sFile
     * @return mixed
     */
    public static function alert($sMsg, $sFile = null)
    {
        return self::$_oLogservice->alert($sMsg, $sFile);
    }

    /**
     * @param        $sMsg
     * @param string $sFile
     * @return mixed
     */
    public static function crit($sMsg, $sFile = null)
    {
        return self::$_oLogservice->crit($sMsg, $sFile);
    }

    /**
     * @param        $sMsg
     * @param string $sFile
     * @return mixed
     */
    public static function err($sMsg, $sFile = null)
    {
        return self::$_oLogservice->err($sMsg, $sFile);
    }

    /**
     * @param        $sMsg
     * @param string $sFile
     * @return mixed
     */
    public static function warn($sMsg, $sFile = null)
    {
        return self::$_oLogservice->warn($sMsg, $sFile);
    }

    /**
     * @param        $sMsg
     * @param string $sFile
     * @return mixed
     */
    public static function notice($sMsg, $sFile = null)
    {
        self::$_oLogservice->notice($sMsg, $sFile);
    }

    /**
     * @param        $sMsg
     * @param string $sFile
     * @return mixed
     */
    public static function info($sMsg, $sFile = null)
    {
        return self::$_oLogservice->info($sMsg, $sFile);
    }

    /**
     * @param        $sMsg
     * @param string $sFile
     * @return mixed
     */
    public static function debug($sMsg, $sFile = null)
    {
        return self::$_oLogservice->debug($sMsg, $sFile);
    }

    /**
     * @param \Exception $oEx
     *
     * @return mixed
     */
    public static function logException(\Exception $oEx)
    {
        return self::$_oLogservice->logException($oEx);
    }

    /**
     * @param LogService $oLogService
     */
    public static function setLogService(LogService $oLogService)
    {
        self::$_oLogservice = $oLogService;
    }
}