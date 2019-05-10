<?php
namespace Rindow\Module\Monolog;

use Monolog\Logger;
use Rindow\Container\ConfigurationFactory;

class LoggerFactory
{
    public static function factory($serviceLocator,$component=null,array $factory_args=null)
    {
        $config = ConfigurationFactory::factory($serviceLocator,$component,$factory_args);
        if($component==null)
            $component = 'Monolog\Logger';
        if(!isset($config['loggers'][$component]))
            throw new Exception\DomainException('configuration is not found for monolog logger "'.$component.'".');
        $loggerConfig = $config['loggers'][$component];
        if(isset($loggerConfig['name']))
            $loggerName = $loggerConfig['name'];
        else
            $loggerName = $component;
        $logger = new Logger($loggerName);

        if(isset($loggerConfig['handlers'])) {
            foreach ($loggerConfig['handlers'] as $handlerName => $option) {
                if($option===false)
                    continue;
                if(!isset($config['handlers'][$handlerName]))
                    throw new Exception\DomainException('configuration is not found for monolog handler "'.$handlerName.'".');
                if(!isset($config['handlers'][$handlerName]['component']))
                    throw new Exception\DomainException('component name is not specified for monolog handler "'.$handlerName.'".');
                $handler = $serviceLocator->get($config['handlers'][$handlerName]['component']);
                $logger->pushHandler($handler);
            }
        }
        if(isset($loggerConfig['processors'])) {
            foreach ($loggerConfig['processors'] as $processorName => $option) {
                if(!isset($config['processors'][$processorName]))
                    throw new Exception\DomainException('configuration is not found for monolog processor "'.$processorName.'".');
                $processerConfig = $config['processors'][$processorName];
                if(is_callable($processerConfig)) {
                    $callback = $processerConfig;
                } else if(is_array($processerConfig) &&
                            isset($processerConfig['component'])) {
                    $instance = $serviceLocator->get($processerConfig['component']);
                    if(isset($processerConfig['method']))
                        $callback = array($instance,$processerConfig['method']);
                    else
                        $callback = $instance;
                } else {
                    throw new Exception\DomainException('processor must be a function or set of class and method for "'.$component.'" in monolog processer configuration .');
                }
                $logger->pushProcessor($callback);
            }
        }
        return $logger;
    }
}