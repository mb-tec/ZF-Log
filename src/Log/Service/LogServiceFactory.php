<?php

namespace MBtecZfLog\Service;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

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
     * @param ContainerInterface $container
     * @param                    $requestedName
     * @param array|null         $options
     *
     * @return LogService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $oTransportService = $container->get('mbtec.zfemail.transport.service');
        $aConfig = $container->get('config')['mbtec']['zflog'];

        return new LogService($oTransportService, $aConfig);
    }
}
