<?php

namespace MBtecZfLog;

use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Class        Module
 * @package     MBtecZfLog
 * @author      Matthias Büsing <info@mb-tec.eu>
 * @copyright   2016 Matthias Büsing
 * @license     GNU General Public License
 * @link        http://mb-tec.eu
 */
class Module implements ConfigProviderInterface, ServiceProviderInterface
{
    /**
     * @param MvcEvent $e
     */
    public function onBootstrap(MvcEvent $e)
    {
        Service\StaticLogger::setLogService(
            $e->getApplication()->getServiceManager()->get('mbtec.zf-log.service')
        );
    }

    /**
     * @return mixed
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * @return array
     */
    public function getServiceConfig()
    {
        return [
            'factories' => [
                'mbtec.zf-log.service' => function (ServiceManager $oSm) {
                    $oTransportService = $oSm->get('mbtec.zf-email.transport.service');
                    $aConfig = $oSm->get('config')['mbtec']['zf-log'];

                    return new LogService($oTransportService, $aConfig);
                },
            ],
        ];
    }
}
