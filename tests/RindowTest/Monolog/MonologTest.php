<?php
namespace RindowTest\Monolog\MonologTest;

use PHPUnit\Framework\TestCase;
use Monolog\Logger;
use Rindow\Container\ModuleManager;

class Test extends TestCase
{
    public function setUp()
    {
        if(!file_exists(__DIR__.'/log')) {
	        mkdir(__DIR__.'/log',true);
	        chmod(__DIR__.'/log', 0777);
        }
        $cache = new \Rindow\Stdlib\Cache\SimpleCache\FileCache(array('path'=>__DIR__.'/log'));
        $cache->clear();
    }

	public function testNormal()
	{
		$config = array(
			'module_manager' => array(
				'modules' => array(
					'Rindow\Module\Monolog\Module' => true,
				),
                'enableCache'=>false,
			),
            'monolog' => array(
                'handlers' => array(
                    'default' => array(
                        'path'  => __DIR__.'/log/testNormal-log.log',
                    ),
                ),
            ),
		);
		@unlink(__DIR__.'/log/testNormal-log.log');
		$mm = new ModuleManager($config);
		$logger = $mm->getServiceLocator()->get('Monolog\Logger');
		$logger->debug('debug-debug');
		$log = file_get_contents(__DIR__.'/log/testNormal-log.log');
		$this->assertRegExp( '/logger\.DEBUG\: debug\-debug/',$log);
		@unlink(__DIR__.'/log/testNormal-log.log');
	}

	public function testMultioutput()
	{
		$config = array(
			'module_manager' => array(
				'modules' => array(
					'Rindow\Module\Monolog\Module' => true,
				),
                'enableCache'=>false,
			),
            'container' => array(
                'components' => array(
                    'test.logger.critical.component' => array(
                        'class' => 'Monolog\Handler\StreamHandler',
                        'constructor_args' => array(
                            'stream' => array('config' => 'monolog::handlers::critical.handler::path'),
                            'level'  => array('config' => 'monolog::handlers::critical.handler::level'),
                        ),
                    ),
                ),
            ),
            'monolog' => array(
                'loggers' => array(
                	'Monolog\Logger' => array(
                		'handlers' => array(
                			'critical.handler' => true,
                		),
                	),
                ),
                'handlers' => array(
                    'default' => array(
                        'path'  => __DIR__.'/log/testMultioutput-log.log',
                    ),
                    'critical.handler' => array(
                    	'component' => 'test.logger.critical.component',
                        'path'  => __DIR__.'/log/testMultioutput-critical.log',
                        'level' => Logger::CRITICAL,
                    ),
                ),
            ),
		);
		@unlink(__DIR__.'/log/testMultioutput-log.log');
		@unlink(__DIR__.'/log/testMultioutput-critical.log');
		$mm = new ModuleManager($config);
		$logger = $mm->getServiceLocator()->get('Monolog\Logger');
		$logger->debug('debug-debug');
		$logger->critical('critical-critical');
		$log = file_get_contents(__DIR__.'/log/testMultioutput-log.log');
		$this->assertRegExp( '/logger\.DEBUG\: debug\-debug/',$log);
		$this->assertRegExp( '/logger\.CRITICAL\: critical\-critical/',$log);
		$log = file_get_contents(__DIR__.'/log/testMultioutput-critical.log');
		$this->assertNotRegExp( '/logger\.DEBUG\: debug\-debug/',$log);
		$this->assertRegExp( '/logger\.CRITICAL\: critical\-critical/',$log);
		@unlink(__DIR__.'/log/testMultioutput-log.log');
		@unlink(__DIR__.'/log/testMultioutput-critical.log');
	}

	public function testPsrProcessor()
	{
		$config = array(
			'module_manager' => array(
				'modules' => array(
					'Rindow\Module\Monolog\Module' => true,
				),
                'enableCache'=>false,
			),
            'monolog' => array(
                'handlers' => array(
                    'default' => array(
                        'path'  => __DIR__.'/log/testPsrProcessor-log.log',
                    ),
                ),
            ),
		);
		@unlink(__DIR__.'/log/testPsrProcessor-log.log');
		$mm = new ModuleManager($config);
		$logger = $mm->getServiceLocator()->get('Monolog\Logger');
		$logger->debug('psr-test.{test}.debug',array('test'=>'Foo'));
		$log = file_get_contents(__DIR__.'/log/testPsrProcessor-log.log');
		$this->assertRegExp( '/logger\.DEBUG\: psr-test\.Foo\.debug/',$log);
		@unlink(__DIR__.'/log/testPsrProcessor-log.log');
	}
}