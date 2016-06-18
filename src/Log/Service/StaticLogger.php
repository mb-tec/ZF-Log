<?php

namespace MBtec\Log\Service;

/**
 * Class        StaticLogger
 * @package     MBtec\Log
 * @author      Matthias Büsing <info@mb-tec.eu>
 * @copyright   2016 Matthias Büsing
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 * @link        http://mb-tec.eu
 */
class StaticLogger
{
    protected static $_oServiceManager = null;
    protected static $_oLogservice = null;

    /**
     * @param $oServiceManager
     */
    public static function setServiceManager($oServiceManager)
    {
        self::$_oServiceManager = $oServiceManager;

        return;
    }

    /**
     * @param      $iLevel
     * @param      $sMsg
     * @param null $sFile
     */
    public static function log($iLevel, $sMsg, $sFile = null)
    {
        self::_getLogService()->log($iLevel, $sMsg, $sFile);

        return;
    }

    /**
     * @param        $sMsg
     * @param string $sFile
     * @return mixed
     */
    public static function emerg($sMsg, $sFile = null)
    {
        self::_getLogService()->emerg($sMsg, $sFile);

        return;
    }

    /**
     * @param        $sMsg
     * @param string $sFile
     * @return mixed
     */
    public static function alert($sMsg, $sFile = null)
    {
        self::_getLogService()->alert($sMsg, $sFile);

        return;
    }

    /**
     * @param        $sMsg
     * @param string $sFile
     * @return mixed
     */
    public static function crit($sMsg, $sFile = null)
    {
        self::_getLogService()->crit($sMsg, $sFile);

        return;
    }

    /**
     * @param        $sMsg
     * @param string $sFile
     * @return mixed
     */
    public static function err($sMsg, $sFile = null)
    {
        self::_getLogService()->err($sMsg, $sFile);

        return;
    }

    /**
     * @param        $sMsg
     * @param string $sFile
     * @return mixed
     */
    public static function warn($sMsg, $sFile = null)
    {
        self::_getLogService()->warn($sMsg, $sFile);

        return;
    }

    /**
     * @param        $sMsg
     * @param string $sFile
     * @return mixed
     */
    public static function notice($sMsg, $sFile = null)
    {
        self::_getLogService()->notice($sMsg, $sFile);

        return;
    }

    /**
     * @param        $sMsg
     * @param string $sFile
     * @return mixed
     */
    public static function info($sMsg, $sFile = null)
    {
        self::_getLogService()->info($sMsg, $sFile);

        return;
    }

    /**
     * @param        $sMsg
     * @param string $sFile
     * @return mixed
     */
    public static function debug($sMsg, $sFile = null)
    {
        self::_getLogService()->debug($sMsg, $sFile);

        return;
    }

    /**
     * @param \Exception $oEx
     */
    public static function logException(\Exception $oEx)
    {
        self::_getLogService()->logException($oEx);

        return;
    }

    /**
     * @return \Mbtec\Log\Logservice
     */
    protected static function _getLogService()
    {
        if (self::$_oLogservice === null) {
            /** @var \Mbtec\Log\Logservice _oLogservice */
            self::$_oLogservice = self::$_oServiceManager->get('mbtec.log.service');
        }

        return self::$_oLogservice;
    }
}