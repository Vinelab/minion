<?php

use Mockery as M;
use Vinelab\Minion\Client;

/**
 * @author Abed Halawi <abed.halawi@vinelab.com>
 */
class ClientTest extends UnitTestCase {

    public function setUp()
    {
        parent::setUp();

        $this->client = M::mock(new Client('i.the.divine', []));
    }

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

    public function test_preparing_topic()
    {
        $this->client->setTopicPrefix('some.topic.prefix.');

        $prepareTopic = $this->unProtectMethod('prepareTopic', $this->client);
        $this->assertEquals('some.topic.prefix.my.toppps', $prepareTopic->invokeArgs($this->client, ['my.toppps']));

        $this->assertEquals('some.topic.prefix.', $this->client->getTopicPrefix());
    }

    public function test_subscribing_with_provider_method()
    {
        $session = M::mock('Thruway\ClientSession');
        $promise = M::mock('React\Promise\Promise');
        $session->shouldReceive('subscribe')
            ->once()
            ->with('my.topic', $this->getProxyCallbackMock(), null)
            ->andReturn($promise);

        $this->client->shouldReceive('getSession')
            ->once()
            ->withNoArgs()
            ->andReturn($session);

        $subscribe = $this->unProtectMethod('subscribe', $this->client);
        $got = $subscribe->invokeArgs($this->client, ['my.topic', 'bra']);

        $this->assertEquals($got, $promise);
    }

    public function test_subscribing_with_options()
    {
        $session = M::mock('Thruway\ClientSession');
        $promise = M::mock('React\Promise\Promise');

        $session->shouldReceive('subscribe')->once()
            ->with('my.topic', $this->getProxyCallbackMock(), ['option', 'option0'])
            ->andReturn($promise);

        $this->client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);

        $subscribe = $this->unProtectMethod('subscribe', $this->client);
        $got = $subscribe->invokeArgs($this->client, ['my.topic', 'bra', ['option', 'option0']]);

        $this->assertEquals($promise, $got);
    }

    public function test_registering_with_options()
    {
        $session = M::mock('Thruway\ClientSession');
        $this->client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);

        $callee = M::mock('Thruway\Role\Callee');
        $promise = M::mock('React\Promise\Promise');
        $callee->shouldReceive('register')->once()
            ->with($session, 'reg.topic', $this->getProxyCallbackMock(), ['option', 'option0'])
            ->andReturn($promise);

        $this->client->shouldReceive('getCallee')->once()->withNoArgs()->andReturn($callee);

        $register = $this->unProtectMethod('register', $this->client);
        $got = $register->invokeArgs($this->client, ['reg.topic', 'bra', ['option', 'option0']]);

        $this->assertEquals($got, $promise);
    }

    public function test_registering_with_function()
    {
        $session = M::mock('Thruway\ClientSession');
        $this->client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);

        $callee = M::mock('Thruway\Role\Callee');
        $promise = M::mock('React\Promise\Promise');
        $callee->shouldReceive('register')->once()->with($session, 'reg.topic', $this->getProxyCallbackMock(), ['option', 'option0'])->andReturn($promise);
        $this->client->shouldReceive('getCallee')->once()->withNoArgs()->andReturn($callee);

        $register = $this->unProtectMethod('register', $this->client);
        $got = $register->invokeArgs($this->client, ['reg.topic', 'bra', ['option', 'option0'], true]);

        $this->assertEquals($got, $promise);
    }

    public function test_registering_prepares_topic()
    {
        $this->client->setTopicPrefix('test.test.reg.prefix.');

        $session = M::mock('Thruway\ClientSession');
        $this->client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);

        $callee = M::mock('Thruway\Role\Callee');
        $promise = M::mock('React\Promise\Promise');
        $callee->shouldReceive('register')->once()
            ->with($session, 'test.test.reg.prefix.reg.topic', M::type('Closure'), ['option', 'option0'])
            ->andReturn($promise);

        $this->client->shouldReceive('getCallee')->once()->withNoArgs()->andReturn($callee);

        $register = $this->unProtectMethod('register', $this->client);
        $got = $register->invokeArgs($this->client, ['reg.topic', 'bra', ['option', 'option0'], true]);

        $this->assertEquals($got, $promise);
    }

    public function test_calling_simple()
    {
        $session = M::mock('Thruway\ClientSession');
        $promise = M::mock('React\Promise\Promise');
        $session->shouldReceive('call')->once()
            ->with('pub.topic', ['dddddata' => 'hhhhhere'], null, null)
            ->andReturn($promise);

        $this->client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);

        $call = $this->unProtectMethod('call', $this->client);
        $got = $call->invokeArgs($this->client, ['pub.topic', ['dddddata' => 'hhhhhere']]);

        $this->assertEquals($got, $promise);
    }

    public function test_calling_full()
    {
        $topic       = 'pub.topic';
        $arguments   = ['dddddata' => 'hhhhhere'];
        $argumentsKw = ['arg1', 'argw'];
        $options     = ['some', 'options', 'heeere'];

        $session = M::mock('Thruway\ClientSession');
        $promise = M::mock('React\Promise\Promise');
        $session->shouldReceive('call')->once()->with($topic, $arguments, $argumentsKw, $options)->andReturn($promise);

        $this->client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);

        $call = $this->unProtectMethod('call', $this->client);
        $got = $call->invokeArgs($this->client, [$topic, $arguments, $argumentsKw, $options]);

        $this->assertEquals($got, $promise);
    }

    public function test_calling_prepares_topic()
    {
        $topic       = 'pub.topic';
        $prefixed    = 'test.test.pub.test.pub.topic';
        $arguments   = ['dddddata' => 'hhhhhere'];
        $argumentsKw = ['arg1', 'argw'];
        $options     = ['some', 'options', 'heeere'];

        $this->client->setTopicPrefix('test.test.pub.test.');

        $session = M::mock('Thruway\ClientSession');
        $promise = M::mock('React\Promise\Promise');
        $session->shouldReceive('call')->once()->with($prefixed, $arguments, $argumentsKw, $options)->andReturn($promise);

        $this->client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);

        $call = $this->unProtectMethod('call', $this->client);
        $got = $call->invokeArgs($this->client, [$topic, $arguments, $argumentsKw, $options]);

        $this->assertEquals($got, $promise);
    }

    public function test_registering_with_provider_method()
    {
        $session = M::mock('Thruway\ClientSession');
        $this->client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);

        $callee = M::mock('Thruway\Role\Callee');
        $promise = M::mock('React\Promise\Promise');
        $callee->shouldReceive('register')->once()->with($session, 'reg.topic', $this->getProxyCallbackMock(), null)->andReturn($promise);
        $this->client->shouldReceive('getCallee')->once()->withNoArgs()->andReturn($callee);

        $register = $this->unProtectMethod('register', $this->client);
        $got = $register->invokeArgs($this->client, ['reg.topic', 'bra']);

        $this->assertEquals($got, $promise);
    }

    public function test_registering_with_closure()
    {
        $session = M::mock('Thruway\ClientSession');
        $this->client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);

        $callee = M::mock('Thruway\Role\Callee');
        $promise = M::mock('React\Promise\Promise');
        $callee->shouldReceive('register')->once()->with($session, 'reg.topic', $this->getProxyCallbackMock(), null)->andReturn($promise);
        $this->client->shouldReceive('getCallee')->once()->withNoArgs()->andReturn($callee);

        $callMe = function () { return true; };
        $register = $this->unProtectMethod('register', $this->client);
        $got = $register->invokeArgs($this->client, ['reg.topic', $callMe]);

        $this->assertEquals($got, $promise);
    }

    public function test_publishing_prepares_topic()
    {
        $topic       = 'pub.topic';
        $prefixed    = 'test.test.pub.test.pub.topic';
        $arguments   = ['dddddata' => 'hhhhhere'];
        $argumentsKw = ['arg1', 'argw'];
        $options     = ['some', 'options', 'heeere'];

        $this->client->setTopicPrefix('test.test.pub.test.');

        $session = M::mock('Thruway\ClientSession');
        $promise = M::mock('React\Promise\Promise');
        $session->shouldReceive('publish')->once()->with($prefixed, $arguments, $argumentsKw, $options)->andReturn($promise);

        $this->client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);

        $publish = $this->unProtectMethod('publish', $this->client);
        $got = $publish->invokeArgs($this->client, [$topic, $arguments, $argumentsKw, $options]);

        $this->assertEquals($got, $promise);
    }

    public function test_publishing_simple()
    {
        $session = M::mock('Thruway\ClientSession');
        $promise = M::mock('React\Promise\Promise');
        $session->shouldReceive('publish')->once()
            ->with('pub.topic', ['dddddata' => 'hhhhhere'], null, null)
            ->andReturn($promise);

        $this->client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);

        $publish = $this->unProtectMethod('publish', $this->client);
        $got = $publish->invokeArgs($this->client, ['pub.topic', ['dddddata' => 'hhhhhere']]);

        $this->assertEquals($got, $promise);
    }

    public function test_subscribing_prepares_topic()
    {
        $this->client->setTopicPrefix('test.test.test.prefix.');
        $session = M::mock('Thruway\ClientSession');
        $promise = M::mock('React\Promise\Promise');
        $session->shouldReceive('subscribe')->once()
            ->with('test.test.test.prefix.my.topic', $this->getProxyCallbackMock(), ['option', 'option0'])
            ->andReturn($promise);

        $this->client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);

        $subscribe = $this->unProtectMethod('subscribe', $this->client);
        $got = $subscribe->invokeArgs($this->client, ['my.topic', 'whateva', ['option', 'option0'], true]);

        $this->assertEquals($got, $promise);
    }

    public function test_subscribing_with_function()
    {
        $session = M::mock('Thruway\ClientSession');
        $promise = M::mock('React\Promise\Promise');
        $session->shouldReceive('subscribe')->once()->with('my.topic', $this->getProxyCallbackMock(), ['option', 'option0'])->andReturn($promise);

        $this->client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);

        $subscribe = $this->unProtectMethod('subscribe', $this->client);
        $got = $subscribe->invokeArgs($this->client, ['my.topic', 'whateva', ['option', 'option0'], true]);

        $this->assertEquals($got, $promise);
    }

    public function test_subscribing_with_closure()
    {
        $session = M::mock('Thruway\ClientSession');
        $promise = M::mock('React\Promise\Promise');
        $callMe = function () { return true; };
        $session->shouldReceive('subscribe')->once()->with('my.topic', $this->getProxyCallbackMock(), ['option', 'option0'])->andReturn($promise);

        $this->client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);

        $subscribe = $this->unProtectMethod('subscribe', $this->client);
        $got = $subscribe->invokeArgs($this->client, ['my.topic', $callMe, ['option', 'option0']]);

        $this->assertEquals($got, $promise);
    }

    public function test_publishing_full()
    {
        $topic       = 'pub.topic';
        $arguments   = ['dddddata' => 'hhhhhere'];
        $argumentsKw = ['arg1', 'argw'];
        $options     = ['some', 'options', 'heeere'];

        $session = M::mock('Thruway\ClientSession');
        $promise = M::mock('React\Promise\Promise');
        $session->shouldReceive('publish')->once()->with($topic, $arguments, $argumentsKw, $options)->andReturn($promise);

        $this->client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);

        $publish = $this->unProtectMethod('publish', $this->client);
        $got = $publish->invokeArgs($this->client, [$topic, $arguments, $argumentsKw, $options]);

        $this->assertEquals($got, $promise);
    }

    protected function getProxyCallbackMock()
    {
        return M::on(function ($proxy) {
            $this->assertTrue(is_callable($proxy));

            $reflection = new ReflectionFunction($proxy);
            $params  = $reflection->getParameters();

            $this->assertEquals('args', $params[0]->name);
            $this->assertEquals('kwArgs', $params[1]->name);

            return true;
        });
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
