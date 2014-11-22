<?php

use Vinelab\Minion\Client;

class ClientTest extends PHPUnit_Framework_TestCase {

    public function test_initializing_sets_providers()
    {
        $providers = ['prov1', 'prov2'] ;
        $c = new Client('julia.dream', $providers);
        $this->assertEquals($providers, $c->getProviders());
    }

    public function test_on_session_start_boots_providers()
    {
        $called = [];

        $bootMe = function ($client) use (&$called) {
            $called['bootMe'] = $client;
        };

        $andMe = function ($client)  use (&$called) {
            $called['andMe'] = $client;
        };

        $providers = ['ClientTestProviderStub', $bootMe, $andMe];
        $c = new Client('cirrus.minor', $providers);
        $c->onSessionStart('session', 'transport');

        $this->assertArrayHasKey('bootMe', $called);
        $this->assertInstanceOf('Vinelab\Minion\Client', $called['bootMe']);
        $this->assertArrayHasKey('andMe', $called);
        $this->assertInstanceOf('Vinelab\Minion\Client', $called['andMe']);
    }
}


class ClientTestProviderStub extends PHPUnit_Framework_TestCase {

    public function __construct($client)
    {
        $this->assertInstanceOf('Vinelab\Minion\Client', $client);
    }

    public function boot()
    {
    }
}
