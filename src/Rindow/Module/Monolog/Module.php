<?php
namespace Rindow\Module\Monolog;

use Monolog\Logger;

class Module
{
    public function getConfig()
    {
        return array(
            'container' => array(
                'aliases' => array(
                    'Logger' => 'Monolog\\Logger',
                ),
                'components' => array(
                    'Monolog\\Logger' => array(
                        'class' => 'Monolog\\Logger',
                        'factory' => 'Rindow\\Module\\Monolog\\LoggerFactory::factory',
                        'factory_args' => array('config'=>'monolog'),
                    ),
                    'Rindow\\Module\\Monolog\\DefaultHandler' => array(
                        'class' => 'Monolog\\Handler\\StreamHandler',
                        'constructor_args' => array(
                            'stream' => array('config' => 'monolog::handlers::default::path'),
                            'level'  => array('config' => 'monolog::handlers::default::level'),
                        ),
                    ),
                    'Rindow\\Module\\Monolog\\SyslogHandler' => array(
                        'class' => 'Monolog\\Handler\\SyslogHandler',
                        'constructor_args' => array(
                            'ident'    => array('config' => 'monolog::handlers::syslog::ident'),
                            'facility' => array('config' => 'monolog::handlers::syslog::facility'),
                            'level'    => array('config' => 'monolog::handlers::syslog::level'),
                            'bubble'   => array('config' => 'monolog::handlers::syslog::bubble'),
                        ),
                    ),
                    'Monolog\\Processor\\PsrLogMessageProcessor' => array(
                    ),
                ),
            ),
            'monolog' => array(
                'loggers' => array(
                    'Monolog\\Logger' => array(
                        'name' => 'logger',
                        'handlers' => array(
                            'default' => true,
                        ),
                        'processors' => array(
                            'default' => true,
                        ),
                    ),
                ),
                'handlers' => array(
                    'default' => array(
                        'component' => 'Rindow\\Module\\Monolog\\DefaultHandler',
                        'path'  => 'php://stdout',
                        'level' => Logger::DEBUG,
                    ),
                    'syslog' => array(
                        'component' => 'Rindow\\Module\\Monolog\\SyslogHandler',
                        'ident'     => 'rindow', 
                        'facility'  => LOG_USER, 
                        'level'     => Logger::DEBUG,
                        'bubble'    => true,
                        'logopts'   => LOG_PID,
                    ),
                ),
                'processors' => array(
                    'default' => array(
                        'component' => 'Monolog\\Processor\\PsrLogMessageProcessor',
                    ),
                ),
            ),
        );
    }
}
