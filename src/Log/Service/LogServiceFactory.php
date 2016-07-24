<?php

namespace MBtecZfLog\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class        LogServiceFactory
 * @package     MBtecZfLog\Service
 * @author      Matthias Büsing <info@mb-tec.eu>
 * @copyright   2016 Matthias Büsing
 * @license     GNU General Public License
 * @link        http://mb-tec.eu
 */
class LogServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $oTransportService = $serviceLocator->get('mbtec.zfemail.transport.service');
        $aConfig = $serviceLocator->get('config')['mbtec']['zflog'];

        return new LogService($oTransportService, $aConfig);
    }
}
