<?php

namespace MBtecZfLog\Service;

use Exception;
use Zend\Log\Formatter\Simple as SimpleFormatter;
use Zend\Mail\Message as MailMessage;
use Zend\Log\Logger;
use Zend\Log\LoggerInterface;
use Zend\Log\Writer\Stream;
use Zend\Log\Writer\Mail;
use Zend\Log\Filter\Priority as FilterPriority;
use MBtecZfEmail\Service\Transport;
use MBtecZfLog\Formatter;
use MBtecZfLog\Writer\Graylog2;

/**
 * Class        LogService
 * @package     MBtecZfLog\Service
 * @author      Matthias Büsing <info@mb-tec.eu>
 * @copyright   2016 Matthias Büsing
 * @license     GNU General Public License
 * @link        http://mb-tec.eu
 */
class LogService implements LoggerInterface
{
    protected $_aLoggers = [];
    protected $_aConfig = null;
    protected $_oTransportService = null;
    protected $_oDefaultFormatter = null;

    const DEFAULT_LOG_DIR = 'data/log';
    const DEFAULT_FILENAME = 'system.log';
    const DEFAULT_FILENAME_EXCEPTION = 'exception.log';
    const DEFAULT_FORMATTER_TEMPLATE = '%timestamp% %priorityName%: %requestMethod% %requestUri%
REQUEST: %requestData%
TIME: %timeElapsed%s
ADDRESS: %remoteAddress%
USER AGENT: %httpUserAgent%
FILE: %file%
LINE: %line%
MESSAGE:
%message%
BACKTRACE:
%backtrace%
';

    /**
     * LogService constructor.
     *
     * @param Transport $oTransportService
     * @param array     $aConfig
     */
    public function __construct(Transport $oTransportService, array $aConfig)
    {
        $this->_oTransportService = $oTransportService;
        $this->_aConfig = $aConfig;

        $aOptions = [
            'exceptionhandler' => true,
            'errorhandler' => true,
            //'fatal_error_shutdownfunction' => true,
        ];

        $this->_getLogger('system.log', $aOptions);
    }

    /**
     * @param       $sFile
     * @param array $aOptions
     *
     * @return mixed
     */
    protected function _getLogger($sFile, array $aOptions = [])
    {
        $sKey = md5($sFile);

        if (!isset($this->_aLoggers[$sKey])) {
            $aWriterConfig = $this->_aConfig['writer'];

            $oLogger = new Logger($aOptions);

            if (isset($aWriterConfig['file']['enabled']) && $aWriterConfig['file']['enabled']) {
                $oFileWriter = $this->_getFileWriter($sFile);
                if (is_object($oFileWriter)) {
                    $oLogger->addWriter($oFileWriter);
                }
            }

            if (isset($aWriterConfig['mail']['enabled']) && $aWriterConfig['mail']['enabled']) {
                $oMailWriter = $this->_getMailWriter();
                if (is_object($oMailWriter)) {
                    $oLogger->addWriter($oMailWriter);
                }
            }

            if (isset($aWriterConfig['graylog']['enabled']) && $aWriterConfig['graylog']['enabled']) {
                $oGraylogWriter = $this->_getGraylogWriter($sFile);
                if (is_object($oGraylogWriter)) {
                    $oLogger->addWriter($oGraylogWriter);
                }
            }

            $this->_aLoggers[$sKey] = $oLogger;
        }

        return $this->_aLoggers[$sKey];
    }

    /**
     * @param      $iLevel
     * @param      $sMsg
     * @param null $sFile
     *
     * @throws Exception
     */
    public function log($iLevel, $sMsg, $sFile = null)
    {
        $this->_log($iLevel, $sMsg, $this->_getLogfileName($sFile));

        return;
    }

    /**
     * @param      $sMsg
     * @param null $sFile
     *
     * @return $this
     * @throws Exception
     */
    public function emerg($sMsg, $sFile = null)
    {
        $this->_log(Logger::EMERG, $sMsg, $this->_getLogfileName($sFile));

        return;
    }

    /**
     * @param      $sMsg
     * @param null $sFile
     *
     * @return $this
     * @throws Exception
     */
    public function alert($sMsg, $sFile = null)
    {
        $this->_log(Logger::ALERT, $sMsg, $this->_getLogfileName($sFile));

        return;
    }

    /**
     * @param      $sMsg
     * @param null $sFile
     *
     * @return $this
     * @throws Exception
     */
    public function crit($sMsg, $sFile = null)
    {
        $this->_log(Logger::CRIT, $sMsg, $this->_getLogfileName($sFile));

        return;
    }

    /**
     * @param      $sMsg
     * @param null $sFile
     *
     * @return $this
     * @throws Exception
     */
    public function err($sMsg, $sFile = null)
    {
        $this->_log(Logger::ERR, $sMsg, $this->_getLogfileName($sFile));

        return;
    }

    /**
     * @param      $sMsg
     * @param null $sFile
     *
     * @return $this
     * @throws Exception
     */
    public function warn($sMsg, $sFile = null)
    {
        $this->_log(Logger::WARN, $sMsg, $this->_getLogfileName($sFile));

        return;
    }

    /**
     * @param      $sMsg
     * @param null $sFile
     *
     * @return $this
     * @throws Exception
     */
    public function notice($sMsg, $sFile = null)
    {
        $this->_log(Logger::NOTICE, $sMsg, $this->_getLogfileName($sFile));

        return;
    }

    /**
     * @param      $sMsg
     * @param null $sFile
     *
     * @return $this
     * @throws Exception
     */
    public function info($sMsg, $sFile = null)
    {
        $this->_log(Logger::INFO, $sMsg, $this->_getLogfileName($sFile));

        return;
    }

    /**
     * @param      $sMsg
     * @param null $sFile
     *
     * @return $this
     * @throws Exception
     */
    public function debug($sMsg, $sFile = null)
    {
        $this->_log(Logger::DEBUG, $sMsg, $this->_getLogfileName($sFile));

        return;
    }

    /**
     * @param        $sMsg
     * @param null   $iLevel
     * @param string $sFile
     *
     * @throws Exception
     */
    protected function _log($iLevel, $sMsg, $sFile)
    {
        if (is_array($sMsg)) {
            $sMsg = print_r($sMsg, true);
        } elseif (is_object($sMsg)) {
            $sMsg = (string)$sMsg;
        }

        $sMsg = PHP_EOL . $sMsg . PHP_EOL;

        try {
            $oLogger = $this->_getLogger($sFile);
            if (is_object($oLogger) && $oLogger->getWriters()->count() > 0) {
                $oLogger->log($iLevel, $sMsg);
            }
        } catch (\Exception $oEx) {
            //
        }

        return;
    }

    /**
     * @param Exception $oEx
     */
    public function logException(Exception $oEx)
    {
        $sFile = isset($this->_aConfig['writer']['file']['exception_log_filename'])
            ? basename($this->_aConfig['writer']['file']['exception_log_filename'])
            : self::DEFAULT_FILENAME_EXCEPTION;

        $this->_log(Logger::ERR, $oEx, $sFile);

        return;
    }

    /**
     * @param $sFile
     *
     * @return Stream
     */
    protected function _getFileWriter($sFile)
    {
        $sLogDir = isset($this->_aConfig['writer']['file']['data_dir'])
            ? $this->_aConfig['writer']['file']['data_dir']
            : self::DEFAULT_LOG_DIR;

        $oWriter = new Stream($sLogDir . DIRECTORY_SEPARATOR . $sFile);
        $oWriter->setFormatter($this->_getDefaultFormatter());

        return $oWriter;
    }

    /**
     * @return Mail
     */
    protected function _getMailWriter()
    {
        $oMailMessage = new MailMessage();

        if (isset($this->_aConfig['writer']['mail']['sender_mail'])
            && isset($this->_aConfig['writer']['mail']['sender_name'])
        ) {
            $oMailMessage->setFrom(
                $this->_aConfig['writer']['mail']['sender_mail'], $this->_aConfig['writer']['mail']['sender_name']);
        }

        foreach ((array)$this->_aConfig['writer']['mail']['receivers'] as $sRec) {
            $oMailMessage->addTo($sRec);
        }

        $oWriter = new Mail($oMailMessage, $this->_oTransportService->getTransport());
        $oWriter->setFormatter($this->_getDefaultFormatter());

        if (isset($this->_aConfig['writer']['mail']['subject_prepend_text'])) {
            $oWriter->setSubjectPrependText($this->_aConfig['writer']['mail']['subject_prepend_text']);
        }

        $oFilter = new FilterPriority(Logger::ERR);
        $oWriter->addFilter($oFilter);

        return $oWriter;
    }

    /**
     * @return \MBtec\Log\Writer\Graylog2
     */
    protected function _getGraylogWriter($sFilename)
    {
        $oFormatter = new Formatter\Gelf();
        $oFormatter
            ->setFacility($this->_aConfig['writer']['graylog']['stream'])
            ->setLogname($sFilename);

        $oWriter = new Graylog2();
        $oWriter
            ->setTransportData($this->_aConfig['writer']['graylog']['transport'])
            ->setFormatter($oFormatter);

        return $oWriter;
    }

    /**
     * @return Formatter\File|null
     */
    protected function _getDefaultFormatter()
    {
        if ($this->_oDefaultFormatter === null) {
//            $sTemplate = isset($this->_aConfig['template'])
//                ? $this->_aConfig['template']
//                : self::DEFAULT_FORMATTER_TEMPLATE;
//
//            $sTemplate = str_replace('\n', PHP_EOL, $sTemplate);

//            $this->_oDefaultFormatter = new Formatter\File($sTemplate);
            $this->_oDefaultFormatter = new SimpleFormatter();
        }

        return $this->_oDefaultFormatter;
    }

    /**
     * @param $sLogfile
     *
     * @return mixed|string
     */
    protected function _getLogfileName($sLogfile)
    {
        if (is_string($sLogfile) && $sLogfile != '') {
            $sFile = $sLogfile;
        } else {
            $sFile = isset($this->_aConfig['writer']['file']['default_log_filename'])
                ? $this->_aConfig['writer']['file']['default_log_filename']
                : self::DEFAULT_FILENAME;
        }

        $sFile = basename($sFile);
        $sFile = str_replace('\\', '_', $sFile);

        return $sFile;
    }
}
