<?php

namespace MBtecZfLog;

use Zend\Log;
use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;

/**
 * Class        Module
 * @package     MBtecZfLog
 * @author      Matthias Büsing <info@mb-tec.eu>
 * @copyright   2016 Matthias Büsing
 * @license     GNU General Public License
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

        $oLogger = $sm->get('mbtec.zflog.service')->getDefaultLogger();

        Log\Logger::registerErrorHandler($oLogger);
        Log\Logger::registerExceptionHandler($oLogger);
//        Log\Logger::registerFatalErrorShutdownFunction($oLogger);

        Service\StaticLogger::setLogService(
            $e->getApplication()->getServiceManager()->get('mbtec.zflog.service')
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
                'mbtec.zflog.service' => function ($sm) {
                    return new Service\LogService(
                        $sm->get('mbtec.zfemail.transport.service'),
                        $sm->get('config')['mbtec']['zflog']
                    );
                },
            ],
        ];
    }
}
