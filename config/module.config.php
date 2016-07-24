<?php

namespace MBtecZfLog;

/**
 * @author      Matthias Büsing <info@mb-tec.eu>
 * @copyright   2016 Matthias Büsing
 * @license     GNU General Public License
 * @link        http://mb-tec.eu
 */
return [
    'service_manager' => [
        'factories' => [
            'mbtec.zflog.service' => Service\LogServiceFactory::class,
        ],
    ],
    'mbtec' => [
        'zflog' => [
            'filename_default_log' => 'system.log',
            'filename_exception_log' => 'exception.log',
            'writer' => [
                'file' => [
                    'enabled' => true,
                ],
                'mail' => [
                    'enabled' => false,
                ],
                'graylog' => [
                    'enabled' => false,
                ],
            ],
        ],
    ],
];
