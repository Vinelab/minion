<?php

use Mockery as M;
use Vinelab\Minion\Client;

/**
 * @author Abed Halawi <abed.halawi@vinelab.com>
 */
class ProviderTest extends UnitTestCase
{
    public function setUp(): void
    {
        $this->client = new Client('the-realm', []);
        $this->provider = new ProviderStub($this->client);
    }

    public function tearDown(): void
    {
        M::close();
    }

    public function test_initializing_sets_client(): void
    {
        $getClient = $this->unProtectMethod('getClient', $this->provider);
        $this->assertEquals($this->client, $getClient->invoke($this->provider));
    }
}

class ProviderStub extends \Vinelab\Minion\Provider
{
    public function boot()
    {
    }

    public function bra()
    {
        return 'bra';
    }
}
