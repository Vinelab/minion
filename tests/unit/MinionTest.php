<?php

use Vinelab\Minion\Minion;

/**
 * @author Abed Halawi <abed.halawi@vinelab.com>
 */
class MinionTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->m = new Minion();
    }

    /**
     * @expectedException Vinelab\Minion\InvalidProviderException
     * @expectedExceptionMessage Provider NonProvider must be an instance of \Vinelab\Minion\Provider
     */
    public function test_registering_non_provider_fails()
    {
        $this->m->register('NonProvider');
    }

    public function test_does_not_register_duplicates()
    {
        $this->m->register('AProvider');
        $this->m->register('AProvider');

        $this->assertCount(1, $this->m->getRegisteredProviders());
    }

    public function test_default_config()
    {
        $default = [
            'realm' => 'minion',
            'host' => '127.0.0.1',
            'port' => 9090,
            'debug' => false,
            'tls' => false,
            'path' => '/ws',
        ];

        $this->assertEquals($default, $this->m->getConfig());
    }

    public function test_getting_config_param()
    {
        $this->assertEquals('minion', $this->m->getConfig('realm'));
    }

    public function test_merging_config()
    {
        $options = [
            'realm' => 'secrets',
            'port' => 19695,
        ];

        $merged = array_merge($this->m->getConfig(), $options);
        $this->m->mergeConfig($options);
        $this->assertEquals($merged, $this->m->getConfig());
    }
}

class NonProvider
{
}

class AProvider extends Vinelab\Minion\Provider
{
    public function boot()
    {
    }
}
