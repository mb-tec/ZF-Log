<?php

namespace MBtecZfLog\Formatter;

use Zend\Log\Formatter\Base;
use Gelf\Message;

/**
 * Class        Gelf
 * @package     MBtecZfLog\Formatter
 * @author      Matthias Büsing <info@mb-tec.eu>
 * @copyright   2016 Matthias Büsing
 * @license     GNU General Public License
 * @link        http://mb-tec.eu
 */
class Gelf extends Base
{
    private $_sFacility = 'ZF2';
    private $_sLogname;

    /**
     * @param $sFacility
     *
     * @return $this
     */
    public function setFacility($sFacility)
    {
        $this->_sFacility = $sFacility;

        return $this;
    }

    /**
     * @param $sLogname
     *
     * @return $this
     */
    public function setLogname($sLogname)
    {
        $this->_sLogname = $sLogname;

        return $this;
    }

    /**
     * @param array $event
     *
     * @return Message
     */
    public function format($event)
    {
        $message = new Message;
        $message->setHost(gethostname());

        if (isset($event['priority'])) {
            $message->setLevel($event['priority']);
        } else if (isset($event['errno'])) {
            // @todo Convert to syslog error levels?
            $message->setLevel($event['errno']);
        }

        $message->setFullMessage($event['message']);
        $message->setShortMessage($event['message']);

        if (isset($event['full'])) {
            $message->setFullMessage($event['full']);
        }

        if (isset($event['short'])) {
            $message->setShortMessage($event['short']);
        }

        if (isset($event['file'])) {
            $message->setFile($event['file']);
        }

        if (isset($event['line'])) {
            $message->setLine($event['line']);
        }

        if (isset($event['version'])) {
            $message->setVersion($event['version']);
        }

        if (isset($event['facility'])) {
            $message->setFacility($event['facility']);
        } else {
            $message->setFacility($this->_sFacility);
        }

        if (isset($event['extra']) && isset($event['extra']['trace'])) {
            $aTraceOut = [];
            foreach ($event['extra']['trace'] as $aTrace) {
                if (isset($aTrace['file']) && isset($aTrace['line'])) {
                    $aTraceOut[] = sprintf('file: %s | line: %s', $aTrace['file'], $aTrace['line']);
                } elseif (isset($aTrace['function']) && isset($aTrace['class']) && isset($aTrace['type'])) {
                    $aTraceOut[] = sprintf('%s %s %s', $aTrace['class'], $aTrace['type'], $aTrace['function']);
                }
            }

            $message->setAdditional('trace', implode(PHP_EOL, $aTraceOut));
        }

        if ($this->_sLogname != '') {
            $message->setAdditional('logname', $this->_sLogname);
        }

        if (isset($event['timestamp'])) {
            if ($event['timestamp'] instanceof \DateTime) {
                $message->setTimestamp($event['timestamp']->getTimestamp());
            } else {
                $message->setTimestamp($event['timestamp']);
            }
        }

        $aBlackKeys = [
            'message', 'priority', 'errno', 'full', 'short', 'file', 'line', 'version', 'facility', 'timestamp',
        ];
        foreach ($event as $mKey => $mValue) {
            if (!in_array($mKey, $aBlackKeys)) {
                if (is_array($mValue)) {
                    $rows = [];
                    foreach ($mValue as $mKey2 => $mValue2) {
                        if (is_array($mValue2)) {
                            continue;
                        }

                        $rows[] = sprintf('%s: %s', $mKey2, $mValue2);
                    }

                    $mValue = implode(' | ', $rows);
                }

                $message->setAdditional($mKey, $mValue);
            }
        }

        return $message;
    }
}
