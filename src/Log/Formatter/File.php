<?php

namespace MBtec\Log\Formatter;

use Zend\Log\Formatter\Simple;

/**
 * Class        Gelf
 * @package     MBtec\Log\Formatter
 * @author      Matthias Büsing <info@mb-tec.eu>
 * @copyright   2016 Matthias Büsing
 * @license     GNU General Public License
 * @link        http://mb-tec.eu
 */
class File extends Simple
{
    /**
     * @param array $aEvent
     * @param bool  $enableBacktrace
     *
     * @return string
     */
    public function format($aEvent, $enableBacktrace = true)
    {
        $this->_addEventMetadata($aEvent, '-', $enableBacktrace);

        return parent::format($aEvent);
    }

    /**
     * @param      $aEvent
     * @param null $notAvailable
     * @param bool $enableBacktrace
     */
    protected function _addEventMetadata(&$aEvent, $notAvailable = null, $enableBacktrace = false)
    {
        $aEvent['backtrace'] = $notAvailable;
        
        if (isset($aEvent['extra']['file'])) {
            $aEvent['file'] = $aEvent['extra']['file'];
        } else {
            $aEvent['file'] = $notAvailable;
        }

        if (isset($aEvent['extra']['line'])) {
            $aEvent['line'] = $aEvent['extra']['line'];
        } else {
            $aEvent['line'] = $notAvailable;
        }

        // Add request time
        if (isset($_SERVER['REQUEST_TIME_FLOAT'])) {
            $aEvent['timeElapsed'] = (float) sprintf('%f', microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']);
        } else {
            $aEvent['timeElapsed'] = (float) sprintf('%d', time() - $_SERVER['REQUEST_TIME']);
        }

        // Find file and line where message originated from and optionally get backtrace lines
        $nextIsFirst = false;                        // Skip backtrace frames until we reach Mage::log(Exception)
        $recordBacktrace = false;
        $maxBacktraceLines = $enableBacktrace ? (int) 50000 : 0;

        if (version_compare(PHP_VERSION, '5.3.6') < 0 ) {
            $debugBacktrace = debug_backtrace(false);
        } elseif (version_compare(PHP_VERSION, '5.4.0') < 0) {
            $debugBacktrace = debug_backtrace(
                $maxBacktraceLines > 0 ? 0 : DEBUG_BACKTRACE_IGNORE_ARGS
            );
        } else {
            $debugBacktrace = debug_backtrace(
                $maxBacktraceLines > 0 ? 0 : DEBUG_BACKTRACE_IGNORE_ARGS,
                $maxBacktraceLines + 10
            );
        }

        $backtraceFrames = [];
        foreach ($debugBacktrace as $frame) {
            // Don't record backtrace for Mage::logException
            if ($frame['function'] == 'logException') {
                continue;
            }
            
//            if (($nextIsFirst && $frame['function'] == 'logException')) {
//                if (isset($frame['file']) && isset($frame['line'])) {
//                    $aEvent['file'] = $frame['file'];
//                    $aEvent['line'] = $frame['line'];
//
//                    if ($maxBacktraceLines) {
//                        $backtraceFrames = [];
//                    } elseif ($nextIsFirst) {
//                        break;
//                    } else {
//                        continue;
//                    }
//                }
//
//
//
//                $nextIsFirst = true;
//                $recordBacktrace = true;
//                continue;
//            }
//
//            if ($recordBacktrace) {
//                if (count($backtraceFrames) >= $maxBacktraceLines) {
//                    break;
//                }
//                $backtraceFrames[] = $frame;
//                continue;
//            }

            $backtraceFrames[] = $frame;
        }

        if (!empty($backtraceFrames)) {
            $backtrace = array();
            foreach ($backtraceFrames as $index => $frame) {
                // Set file
                if (!isset($frame['file']) || $frame['file'] == '') {
                    $frame['file'] = 'unknown_file';
                }

                // Set line
                if (!isset($frame['line']) || $frame['line'] == '') {
                    $frame['line'] = 0;
                }

                $function = (isset($frame['class']) ? "{$frame['class']}{$frame['type']}":'') . $frame['function'];
                $args = [];
                if (isset($frame['args'])) {
                    foreach ($frame['args'] as $value) {
                        $args[] = (is_object($value)
                            ? get_class($value)
                            : ( is_array($value)
                                ? 'array('.count($value).')'
                                : ( is_string($value)
                                    ? "'".(strlen($value) > 28 ? "'".substr($value, 0, 25)."...'" : $value)."'"
                                    : gettype($value)."($value)"
                                )
                            )
                        );
                    }
                }

                $args = implode(', ', $args);
                $backtrace[] = "#{$index} {$frame['file']}:{$frame['line']} $function($args)";
            }

            $aEvent['backtrace'] = implode("\n", $backtrace);
        }

        if (!empty($_SERVER['REQUEST_METHOD'])) {
            $aEvent['requestMethod'] = $_SERVER['REQUEST_METHOD'];
        } else {
            $aEvent['requestMethod'] = php_sapi_name();
        }

        if (!empty($_SERVER['REQUEST_URI'])) {
            $aEvent['requestUri'] = $_SERVER['REQUEST_URI'];
        } else {
            $aEvent['requestUri'] = $_SERVER['PHP_SELF'];
        }

        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            $aEvent['httpUserAgent'] = $_SERVER['HTTP_USER_AGENT'];
        } else {
            $aEvent['httpUserAgent'] = $notAvailable;
        }

        // Fetch request data
        $requestData = [];
        if (!empty($_GET)) {
            $requestData[] = '  GET|'.substr(@json_encode($_GET), 0, 1000);
        }
        if (!empty($_POST)) {
            $requestData[] = '  POST|'.substr(@json_encode($_POST), 0, 1000);
        }
        if (!empty($_FILES)) {
            $requestData[] = '  FILES|'.substr(@json_encode($_FILES), 0, 1000);
        }
        $aEvent['requestData'] = $requestData ? implode("\n", $requestData) : $notAvailable;

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $aEvent['remoteAddress'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $aEvent['remoteAddress'] = $_SERVER['REMOTE_ADDR'];
        } else {
            $aEvent['remoteAddress'] = $notAvailable;
        }

        // Add hostname to log message ...
        if (gethostname() !== false) {
            $aEvent['hostname'] = gethostname();
        } else {
            $aEvent['hostname'] = 'Could not determine hostname !';
        }
    }
}
