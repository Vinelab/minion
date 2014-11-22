<?php

use Mockery as M;

/**
 * @author Abed Halawi <abed.halawi@vinelab.com>
 */
class ProvderTest extends UnitTestCase {

    public function setUp()
    {
        $this->client = M::mock('Vinelab\Minion\Client');
        $this->provider = new ProviderStub($this->client);
    }

    public function tearDown()
    {
        M::close();
    }

    public function test_initializing_sets_client()
    {
        $getClient = $this->unProtectMethod('getClient', $this->provider);
        $this->assertEquals($this->client, $getClient->invoke($this->provider));
    }

    public function test_getting_client_session()
    {
        $session = M::mock('Thruway\ClientSession');
        $this->client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);

        $getSession = $this->unProtectMethod('getSession', $this->provider);
        $this->assertEquals($session, $getSession->invoke($this->provider));
    }

    public function test_getting_callee()
    {
        $callee = M::mock('Thruway\Role\Callee');
        $this->client->shouldReceive('getCallee')->once()->withNoArgs()->andReturn($callee);

        $getCallee = $this->unProtectMethod('getCallee', $this->provider);
        $this->assertEquals($callee, $getCallee->invoke($this->provider));
    }

    public function testPreparingTopic()
    {
        $this->provider->setTopicPrefix('some.topic.prefix.');

        $prepareTopic = $this->unProtectMethod('prepareTopic', $this->provider);
        $this->assertEquals('some.topic.prefix.my.toppps', $prepareTopic->invokeArgs($this->provider, ['my.toppps']));

        $this->assertEquals('some.topic.prefix.', $this->provider->getTopicPrefix());
    }

    public function test_subscribing_with_provider_method()
    {
        $session = M::mock('Thruway\ClientSession');
        $promise = M::mock('React\Promise\Promise');
        $session->shouldReceive('subscribe')->once()->with('my.topic', [$this->provider, 'bra'], null)->andReturn($promise);

        $this->client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);

        $subscribe = $this->unProtectMethod('subscribe', $this->provider);
        $got = $subscribe->invokeArgs($this->provider, ['my.topic', 'bra']);

        $this->assertEquals($got, $promise);
    }

    public function test_subscribing_with_options()
    {
        $session = M::mock('Thruway\ClientSession');
        $promise = M::mock('React\Promise\Promise');
        $session->shouldReceive('subscribe')->once()
            ->with('my.topic', [$this->provider, 'bra'], ['option', 'option0'])
            ->andReturn($promise);

        $this->client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);

        $subscribe = $this->unProtectMethod('subscribe', $this->provider);
        $got = $subscribe->invokeArgs($this->provider, ['my.topic', 'bra', ['option', 'option0']]);

        $this->assertEquals($promise, $got);
    }

    public function test_subscribing_with_closure()
    {
        $session = M::mock('Thruway\ClientSession');
        $callMe = function () { return true; };

        $promise = M::mock('React\Promise\Promise');
        $session->shouldReceive('subscribe')->once()->with('my.topic', $callMe, ['option', 'option0'])->andReturn($promise);

        $this->client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);

        $subscribe = $this->unProtectMethod('subscribe', $this->provider);
        $got = $subscribe->invokeArgs($this->provider, ['my.topic', $callMe, ['option', 'option0']]);

        $this->assertEquals($got, $promise);
    }

    public function test_subscribing_with_function()
    {
        $session = M::mock('Thruway\ClientSession');
        $promise = M::mock('React\Promise\Promise');
        $session->shouldReceive('subscribe')->once()->with('my.topic', 'whateva', ['option', 'option0'])->andReturn($promise);

        $this->client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);

        $subscribe = $this->unProtectMethod('subscribe', $this->provider);
        $got = $subscribe->invokeArgs($this->provider, ['my.topic', 'whateva', ['option', 'option0'], true]);

        $this->assertEquals($got, $promise);
    }

    public function test_subscribing_prepares_topic()
    {
        $this->provider->setTopicPrefix('test.test.test.prefix.');
        $session = M::mock('Thruway\ClientSession');
        $promise = M::mock('React\Promise\Promise');
        $session->shouldReceive('subscribe')->once()
            ->with('test.test.test.prefix.my.topic', 'whateva', ['option', 'option0'])
            ->andReturn($promise);

        $this->client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);

        $subscribe = $this->unProtectMethod('subscribe', $this->provider);
        $got = $subscribe->invokeArgs($this->provider, ['my.topic', 'whateva', ['option', 'option0'], true]);

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

        $publish = $this->unProtectMethod('publish', $this->provider);
        $got = $publish->invokeArgs($this->provider, ['pub.topic', ['dddddata' => 'hhhhhere']]);

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

        $publish = $this->unProtectMethod('publish', $this->provider);
        $got = $publish->invokeArgs($this->provider, [$topic, $arguments, $argumentsKw, $options]);

        $this->assertEquals($got, $promise);
    }

    public function test_publishing_prepares_topic()
    {
        $topic       = 'pub.topic';
        $prefixed    = 'test.test.pub.test.pub.topic';
        $arguments   = ['dddddata' => 'hhhhhere'];
        $argumentsKw = ['arg1', 'argw'];
        $options     = ['some', 'options', 'heeere'];

        $this->provider->setTopicPrefix('test.test.pub.test.');

        $session = M::mock('Thruway\ClientSession');
        $promise = M::mock('React\Promise\Promise');
        $session->shouldReceive('publish')->once()->with($prefixed, $arguments, $argumentsKw, $options)->andReturn($promise);

        $this->client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);

        $publish = $this->unProtectMethod('publish', $this->provider);
        $got = $publish->invokeArgs($this->provider, [$topic, $arguments, $argumentsKw, $options]);

        $this->assertEquals($got, $promise);
    }

    public function test_registering_with_provider_method()
    {
        $session = M::mock('Thruway\ClientSession');
        $this->client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);

        $callee = M::mock('Thruway\Role\Callee');
        $promise = M::mock('React\Promise\Promise');
        $callee->shouldReceive('register')->once()->with($session, 'reg.topic', [$this->provider, 'bra'], null)->andReturn($promise);
        $this->client->shouldReceive('getCallee')->once()->withNoArgs()->andReturn($callee);

        $register = $this->unProtectMethod('register', $this->provider);
        $got = $register->invokeArgs($this->provider, ['reg.topic', 'bra']);

        $this->assertEquals($got, $promise);
    }

    public function test_registering_with_closure()
    {
        $session = M::mock('Thruway\ClientSession');
        $callMe = function () { return true; };
        $this->client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);

        $callee = M::mock('Thruway\Role\Callee');
        $promise = M::mock('React\Promise\Promise');
        $callee->shouldReceive('register')->once()->with($session, 'reg.topic', $callMe, null)->andReturn($promise);
        $this->client->shouldReceive('getCallee')->once()->withNoArgs()->andReturn($callee);

        $register = $this->unProtectMethod('register', $this->provider);
        $got = $register->invokeArgs($this->provider, ['reg.topic', $callMe]);

        $this->assertEquals($got, $promise);
    }

    public function test_registering_with_options()
    {
        $session = M::mock('Thruway\ClientSession');
        $this->client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);

        $callee = M::mock('Thruway\Role\Callee');
        $promise = M::mock('React\Promise\Promise');
        $callee->shouldReceive('register')->once()
            ->with($session, 'reg.topic', [$this->provider, 'bra'], ['option', 'option0'])
            ->andReturn($promise);

        $this->client->shouldReceive('getCallee')->once()->withNoArgs()->andReturn($callee);

        $register = $this->unProtectMethod('register', $this->provider);
        $got = $register->invokeArgs($this->provider, ['reg.topic', 'bra', ['option', 'option0']]);

        $this->assertEquals($got, $promise);
    }

    public function test_registering_with_function()
    {
        $session = M::mock('Thruway\ClientSession');
        $this->client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);

        $callee = M::mock('Thruway\Role\Callee');
        $promise = M::mock('React\Promise\Promise');
        $callee->shouldReceive('register')->once()->with($session, 'reg.topic', 'bra', ['option', 'option0'])->andReturn($promise);
        $this->client->shouldReceive('getCallee')->once()->withNoArgs()->andReturn($callee);

        $register = $this->unProtectMethod('register', $this->provider);
        $got = $register->invokeArgs($this->provider, ['reg.topic', 'bra', ['option', 'option0'], true]);

        $this->assertEquals($got, $promise);
    }

    public function test_registering_prepares_topic()
    {
        $this->provider->setTopicPrefix('test.test.reg.prefix.');

        $session = M::mock('Thruway\ClientSession');
        $this->client->shouldReceive('getSession')->once()->withNoArgs()->andReturn($session);

        $callee = M::mock('Thruway\Role\Callee');
        $promise = M::mock('React\Promise\Promise');
        $callee->shouldReceive('register')->once()
            ->with($session, 'test.test.reg.prefix.reg.topic', 'bra', ['option', 'option0'])
            ->andReturn($promise);

        $this->client->shouldReceive('getCallee')->once()->withNoArgs()->andReturn($callee);

        $register = $this->unProtectMethod('register', $this->provider);
        $got = $register->invokeArgs($this->provider, ['reg.topic', 'bra', ['option', 'option0'], true]);

        $this->assertEquals($got, $promise);
    }
}

class ProviderStub extends \Vinelab\Minion\Provider {

    public function boot()
    {
    }

    public function bra()
    {
        return 'bra';
    }
}
