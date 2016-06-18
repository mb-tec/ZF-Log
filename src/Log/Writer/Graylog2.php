<?php

namespace MBtec\Log\Writer;

use Zend\Log\Writer\AbstractWriter;
use Gelf\Publisher;

/**
 * Class        Graylog2
 * @package     MBtec\Log\Writer
 * @author      Matthias Büsing <info@mb-tec.eu>
 * @copyright   2016 Matthias Büsing
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 * @link        http://mb-tec.eu
 */
class Graylog2 extends AbstractWriter
{
    protected $_oPublisher;
    protected $_sTransportData;

    /**
     * @param array $aTransportData
     *
     * @return $this
     */
    public function setTransportData(array $aTransportData = [])
    {
        $this->_sTransportData = $aTransportData;

        return $this;
    }

    /**
     * @param array $event
     */
    public function doWrite(array $event)
    {
        try {
            $this->_getPublisher()->publish(
                $this->formatter->format($event)
            );
        } catch (\Exception $oEx) {

        }

        return;
    }

    /**
     * @return Publisher
     */
    protected function _getPublisher()
    {
        if (!is_object($this->_oPublisher)) {
            $sTransportClass = sprintf('\Gelf\Transport\%sTransport', $this->_sTransportData['type']);
            $oTransport = new $sTransportClass($this->_sTransportData['host'], $this->_sTransportData['port']);
            $oPublisher = new Publisher($oTransport);

            $this->_oPublisher = $oPublisher;
        }

        return $this->_oPublisher;
    }
}