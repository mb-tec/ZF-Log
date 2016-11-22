<?php

namespace MBtecZfLog;

/**
 * @author      Matthias Büsing <info@mb-tec.eu>
 * @copyright   2016 Matthias Büsing
 * @license     GNU General Public License
 * @link        http://mb-tec.eu
 */
return [
    'mbtec' => [
        'zf-log' => [
            'writer' => [
                'file' => [
                    'enabled' => true,
                    'default_log_filename' => 'system.log',
                    'exception_log_filename' => 'exception.log',
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
