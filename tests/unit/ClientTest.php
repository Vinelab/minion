<?php

use Mockery as M;
use Vinelab\Minion\Client;

/**
 * @author Abed Halawi <abed.halawi@vinelab.com>
 */
class ClientTest extends UnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->client = M::mock(new Client('i.the.divine', []));
    }

    public function tearDown(): void
    {
        M::close();
    }

    public function test_initializing_sets_providers(): void
    {
        $providers = ['prov1', 'prov2'];
        $c = new Client('julia.dream', $providers);
        $this->assertEquals($providers, $c->getProviders());
    }

    public function test_on_session_start_boots_providers(): void
    {
        $called = [];

        $bootMe = function ($client) use (&$called) {
            $called['bootMe'] = $client;
        };

        $andMe = function ($client) use (&$called) {
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

    public function test_preparing_topic(): void
    {
        $this->client->setTopicPrefix('some.topic.prefix.');

        $prepareTopic = $this->unProtectMethod('prepareTopic', $this->client);
        $this->assertEquals('some.topic.prefix.my.toppps', $prepareTopic->invokeArgs($this->client, ['my.toppps']));

        $this->assertEquals('some.topic.prefix.', $this->client->getTopicPrefix());
    }

    public function test_subscribing_with_provider_method(): void
    {
        $session = M::mock('Thruway\ClientSession');
        $promise = M::mock('React\Promise\Promise');
        $session->shouldReceive('subscribe')
            ->once()
            ->with('my.topic', $this->getProxyCallbackMock(), null)
            ->andReturn($promise);

        $client = M::mock('Vinelab\Minion\Client')->makePartial();
        $client->shouldReceive('getSession')
            ->once()
            ->withNoArgs()
            ->andReturn($session);

        $got = $client->subscribe('my.topic', 'bra');

        $this->assertEquals($promise, $got);
    }

    public function test_subscribing_with_options(): void
    {
        $session = M::mock('Thruway\ClientSession');
        $promise = M::mock('React\Promise\Promise');

        $session->shouldReceive('subscribe')->once()
            ->with('my.topic', $this->getProxyCallbackMock(), ['option', 'option0'])
            ->andReturn($promise);

        $client = M::mock('Vinelab\Minion\Client')->makePartial();
        $client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);

        $got = $client->subscribe('my.topic', 'bra', ['option', 'option0']);

        $this->assertEquals($promise, $got);
    }

    public function test_registering_with_options(): void
    {
        $session = M::mock('Thruway\ClientSession');

        $callee = M::mock('Thruway\Role\Callee');
        $promise = M::mock('React\Promise\Promise');
        $callee->shouldReceive('register')->once()
            ->with($session, 'reg.topic', $this->getProxyCallbackMock(), ['option', 'option0'])
            ->andReturn($promise);

        $client = M::mock('Vinelab\Minion\Client')->makePartial();
        $client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);
        $client->shouldReceive('getCallee')->once()->withNoArgs()->andReturn($callee);

        $got = $client->register('reg.topic', 'bra', ['option', 'option0']);

        $this->assertEquals($promise, $got);
    }

    public function test_registering_with_function(): void
    {
        $session = M::mock('Thruway\ClientSession');

        $callee = M::mock('Thruway\Role\Callee');
        $promise = M::mock('React\Promise\Promise');
        $callee->shouldReceive('register')
            ->once()
            ->with($session, 'reg.topic', $this->getProxyCallbackMock(), ['option', 'option0'])
            ->andReturn($promise);

        $client = M::mock('Vinelab\Minion\Client')->makePartial();
        $client->shouldReceive('getCallee')->once()->withNoArgs()->andReturn($callee);
        $client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);

        $got = $client->register('reg.topic', 'bra', ['option', 'option0']);

        $this->assertEquals($promise, $got);
    }

    public function test_registering_prepares_topic(): void
    {
        $session = M::mock('Thruway\ClientSession');

        $callee = M::mock('Thruway\Role\Callee');
        $promise = M::mock('React\Promise\Promise');
        $callee->shouldReceive('register')->once()
            ->with($session, 'test.test.reg.prefix.reg.topic', M::type('Closure'), ['option', 'option0'])
            ->andReturn($promise);

        $client = M::mock('Vinelab\Minion\Client')->makePartial();
        $client->setTopicPrefix('test.test.reg.prefix.');
        $client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);
        $client->shouldReceive('getCallee')->once()->withNoArgs()->andReturn($callee);

        $got = $client->register('reg.topic', 'bra', ['option', 'option0']);

        $this->assertEquals($promise, $got);
    }

    public function test_calling_simple(): void
    {
        $session = M::mock('Thruway\ClientSession');
        $promise = M::mock('React\Promise\Promise');
        $session->shouldReceive('call')->once()
            ->with('pub.topic', ['dddddata' => 'hhhhhere'], null, null)
            ->andReturn($promise);

        $client = M::mock('Vinelab\Minion\Client')->makePartial();
        $client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);

        $got = $client->call('pub.topic', ['dddddata' => 'hhhhhere']);

        $this->assertEquals($promise, $got);
    }

    public function test_calling_full(): void
    {
        $topic = 'pub.topic';
        $arguments = ['dddddata' => 'hhhhhere'];
        $argumentsKw = ['arg1', 'argw'];
        $options = ['some', 'options', 'heeere'];

        $session = M::mock('Thruway\ClientSession');
        $promise = M::mock('React\Promise\Promise');
        $session->shouldReceive('call')->once()->with($topic, $arguments, $argumentsKw, $options)->andReturn($promise);

        $client = M::mock('Vinelab\Minion\Client')->makePartial();
        $client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);

        $got = $client->call($topic, $arguments, $argumentsKw, $options);

        $this->assertEquals($promise, $got);
    }

    public function test_calling_prepares_topic(): void
    {
        $topic = 'pub.topic';
        $prefixed = 'test.test.pub.test.pub.topic';
        $arguments = ['dddddata' => 'hhhhhere'];
        $argumentsKw = ['arg1', 'argw'];
        $options = ['some', 'options', 'heeere'];

        $session = M::mock('Thruway\ClientSession');
        $promise = M::mock('React\Promise\Promise');
        $session->shouldReceive('call')->once()->with($prefixed, $arguments, $argumentsKw, $options)->andReturn($promise);

        $client = M::mock('Vinelab\Minion\Client')->makePartial();
        $client->setTopicPrefix('test.test.pub.test.');
        $client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);

        $got = $client->call($topic, $arguments, $argumentsKw, $options);

        $this->assertEquals($promise, $got);
    }

    public function test_registering_with_provider_method(): void
    {
        $session = M::mock('Thruway\ClientSession');

        $callee = M::mock('Thruway\Role\Callee');
        $promise = M::mock('React\Promise\Promise');
        $callee->shouldReceive('register')
            ->once()
            ->with($session, 'reg.topic', $this->getProxyCallbackMock(), null)
            ->andReturn($promise);

        $client = M::mock('Vinelab\Minion\Client')->makePartial();
        $client->shouldReceive('getSession')->withNoArgs()->andReturn($session);
        $client->shouldReceive('getCallee')->once()->withNoArgs()->andReturn($callee);

        $got = $client->register('reg.topic', 'bra');

        $this->assertEquals($promise, $got);
    }

    public function test_registering_with_closure(): void
    {
        $session = M::mock('Thruway\ClientSession');

        $callee = M::mock('Thruway\Role\Callee');
        $promise = M::mock('React\Promise\Promise');
        $callee->shouldReceive('register')
            ->once()
            ->with($session, 'reg.topic', $this->getProxyCallbackMock(), null)
            ->andReturn($promise);

        $client = M::mock('Vinelab\Minion\Client')->makePartial();
        $client->shouldReceive('getCallee')->once()->withNoArgs()->andReturn($callee);
        $client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);

        $callMe = function () { return true; };

        $got = $client->register('reg.topic', $callMe);

        $this->assertEquals($promise, $got);
    }

    public function test_publishing_prepares_topic(): void
    {
        $topic = 'pub.topic';
        $prefixed = 'test.test.pub.test.pub.topic';
        $arguments = ['dddddata' => 'hhhhhere'];
        $argumentsKw = ['arg1', 'argw'];
        $options = ['some', 'options', 'heeere'];

        $session = M::mock('Thruway\ClientSession');
        $promise = M::mock('React\Promise\Promise');
        $session->shouldReceive('publish')->once()->with($prefixed, $arguments, $argumentsKw, $options)->andReturn($promise);

        $client = M::mock('Vinelab\Minion\Client')->makePartial();
        $client->setTopicPrefix('test.test.pub.test.');
        $client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);

        $got = $client->publish($topic, $arguments, $argumentsKw, $options);

        $this->assertEquals($promise, $got);
    }

    public function test_publishing_simple(): void
    {
        $session = M::mock('Thruway\ClientSession');
        $promise = M::mock('React\Promise\Promise');
        $session->shouldReceive('publish')->once()
            ->with('pub.topic', ['dddddata' => 'hhhhhere'], null, null)
            ->andReturn($promise);

        $client = M::mock('Vinelab\Minion\Client')->makePartial();
        $client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);

        $got = $client->publish('pub.topic', ['dddddata' => 'hhhhhere']);

        $this->assertEquals($promise, $got);
    }

    public function test_subscribing_prepares_topic(): void
    {
        $session = M::mock('Thruway\ClientSession');
        $promise = M::mock('React\Promise\Promise');
        $session->shouldReceive('subscribe')->once()
            ->with('test.test.test.prefix.my.topic', $this->getProxyCallbackMock(), ['option', 'option0'])
            ->andReturn($promise);

        $client = M::mock('Vinelab\Minion\Client')->makePartial();
        $client->setTopicPrefix('test.test.test.prefix.');
        $client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);

        $got = $client->subscribe('my.topic', 'whateva', ['option', 'option0']);

        $this->assertEquals($promise, $got);
    }

    public function test_subscribing_with_function(): void
    {
        $session = M::mock('Thruway\ClientSession');
        $promise = M::mock('React\Promise\Promise');
        $session->shouldReceive('subscribe')->once()->with('my.topic', $this->getProxyCallbackMock(), ['option', 'option0'])->andReturn($promise);

        $client = M::mock('Vinelab\Minion\Client')->makePartial();
        $client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);

        $got = $client->subscribe('my.topic', 'whateva', ['option', 'option0']);

        $this->assertEquals($promise, $got);
    }

    public function test_subscribing_with_closure(): void
    {
        $session = M::mock('Thruway\ClientSession');
        $promise = M::mock('React\Promise\Promise');
        $callMe = function () { return true; };
        $session->shouldReceive('subscribe')->once()->with('my.topic', $this->getProxyCallbackMock(), ['option', 'option0'])->andReturn($promise);

        $client = M::mock('Vinelab\Minion\Client')->makePartial();
        $client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);

        $got = $client->subscribe('my.topic', $callMe, ['option', 'option0']);

        $this->assertEquals($promise, $got);
    }

    public function test_publishing_full(): void
    {
        $topic = 'pub.topic';
        $arguments = ['dddddata' => 'hhhhhere'];
        $argumentsKw = ['arg1', 'argw'];
        $options = ['some', 'options', 'heeere'];

        $session = M::mock('Thruway\ClientSession');
        $promise = M::mock('React\Promise\Promise');
        $session->shouldReceive('publish')->once()->with($topic, $arguments, $argumentsKw, $options)->andReturn($promise);

        $client = M::mock('Vinelab\Minion\Client')->makePartial();
        $client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);

        $got = $client->publish($topic, $arguments, $argumentsKw, $options);

        $this->assertEquals($promise, $got);
    }

    protected function getProxyCallbackMock()
    {
        return M::on(function ($proxy) {
            $this->assertTrue(is_callable($proxy));

            $reflection = new ReflectionFunction($proxy);
            $params = $reflection->getParameters();

            $this->assertEquals('args', $params[0]->name);
            $this->assertEquals('kwArgs', $params[1]->name);

            return true;
        });
    }
}

class ClientTestProviderStub extends PHPUnit\Framework\TestCase
{
    public function __construct($client)
    {
        $this->assertInstanceOf('Vinelab\Minion\Client', $client);
    }

    public function boot()
    {
    }
}
