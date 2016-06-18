<?php

namespace MBtec\Log;

use Zend\Log;
use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;

/**
 * Class        Module
 * @package     MBtec\Log
 * @author      Matthias Büsing <info@mb-tec.eu>
 * @copyright   2016 Matthias Büsing
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 * @link        http://mb-tec.eu
 */
class Module implements AutoloaderProviderInterface, ConfigProviderInterface, ServiceProviderInterface
{
    /**
     * @param MvcEvent $e
     */
    public function onBootstrap(MvcEvent $e)
    {
        $app = $e->getApplication();
        $sm = $app->getServiceManager();

        $oLogger = $sm->get('mbtec.log.service')->getDefaultLogger();

//        Log\Logger::registerErrorHandler($oLogger);
//        Log\Logger::registerExceptionHandler($oLogger);
//        Log\Logger::registerFatalErrorShutdownFunction($oLogger);

        Service\StaticLogger::setServiceManager(
            $e->getApplication()->getServiceManager()
        );
    }

    /**
     * Return MBtec\Log autoload config.
     *
     * @see AutoloaderProviderInterface::getAutoloaderConfig()
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\ClassMapAutoloader' => [
                __DIR__ . '/autoload_classmap.php',
            ],
        ];
    }

    /**
     * Return the MBtec\Log module config.
     *
     * @see ConfigProviderInterface::getConfig()
     * @return array
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
                'mbtec.log.service' => function ($sm) {
                    $aConfig = $sm->get('config');

                    $oLogService = new Service\LogService();

                    if (isset($aConfig['mbtec']['log'])) {
                        $oLogService->setConfig($aConfig['mbtec']['log']);
                    }

                    if ($sm->has('mbtec.email.transport.service')) {
                        $oLogService->setMailTransportService($sm->get('mbtec.email.transport.service'));
                    }

                    return $oLogService;
                },
            ],
        ];
    }
}
