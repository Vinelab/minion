<?php

use Vinelab\Minion\Minion;
use Vinelab\Minion\InvalidProviderException;

/**
 * @author Abed Halawi <abed.halawi@vinelab.com>
 */
class MinionTest extends PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        $this->m = new Minion();
    }

    public function test_registering_non_provider_fails(): void
    {
        $this->expectException(InvalidProviderException::class);
        $this->expectExceptionMessage('Provider NonProvider must be an instance of \Vinelab\Minion\Provider');

        $this->m->register('NonProvider');
    }

    public function test_does_not_register_duplicates(): void
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

    public function test_getting_config_param(): void
    {
        $this->assertEquals('minion', $this->m->getConfig('realm'));
    }

    public function test_merging_config(): void
    {
        $options = [
            'realm' => 'secrets',
            'port' => 19695,
        ];

        $merged = array_merge($this->m->getConfig(), $options);
        $this->m->mergeConfig($options);
        $this->assertEquals($merged, $this->m->getConfig());
    }

    public function test_config_tls(): void
    {
        $this->assertEquals(false, $this->m->getConfig('tls'));
        $this->m->mergeConfig(['tls'=>true]);
        $this->assertEquals(true, $this->m->getConfig('tls'));
    }

    public function test_config_path(): void
    {
        $path = '/websocket';
        $this->assertEquals('/ws', $this->m->getConfig('path'));
        $this->m->mergeConfig(['path'=>$path]);
        $this->assertEquals($path, $this->m->getConfig('path'));
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
