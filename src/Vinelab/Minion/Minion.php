<?php

namespace Vinelab\Minion;

use Closure;
use Thruway\Transport\PawlTransportProvider;
use Thruway\Authentication\ClientWampCraAuthenticator;
use React\EventLoop\LoopInterface;

/**
 * @author Abed Halawi <abed.halawi@vinelab.com>
 */
class Minion
{
    /**
     * Hold the registered providers.
     *
     * @var array
     */
    private $providers = [];

    /**
     * The configuration of this minion.
     *
     * @var array
     */
    private $config = [
        'realm' => 'minion',
        'host' => '127.0.0.1',
        'port' => 9090,
        'debug' => false,
        'tls' => false,
        'path' => '/ws',
    ];

    /**
     * Register the given provider.
     *
     * @param Closure|string $provider
     */
    public function register($provider)
    {
        // When we receive a string we consider it to be a provider, if not we throw them away!
        if (is_string($provider) && !is_a($provider, '\Vinelab\Minion\Provider', true)) {
            throw new InvalidProviderException(
                sprintf('Provider %s must be an instance of \Vinelab\Minion\Provider', $provider)
            );
        }

        if (!in_array($provider, $this->providers)) {
            $this->providers[] = $provider;
        }
    }

    /**
     * Get all the currently registered providers.
     *
     * @return array
     */
    public function getRegisteredProviders()
    {
        return $this->providers;
    }

    /**
     * Run the server.
     *
     * @param array $options
     * @param \React\EventLoop\LoopInterface $loop
     *
     * @throws \Exception If encountered any failure starting the client.
     */
    public function run($options = [], LoopInterface $loop = null)
    {
        $this->mergeConfig($options);

        // Print out our lovely minion.
        echo $this->gimmeASCII()."\n";

        $client = $this->newClient($loop);
        $client->addTransportProvider($this->newTransportProvider());

        $auth = $this->getConfig('auth');
        if (!empty($auth)) {
            $client->addClientAuthenticator(
                $this->newAuthenticator($auth['authid'], $auth['secret'])
            );
        }

        return $client->start($this->getConfig('debug'));
    }

    /**
     * Get a new Client instance.
     * 
     * @param \React\EventLoop\LoopInterface $loop
     *
     * @return \Vinelab\Minion\Client
     */
    public function newClient(LoopInterface $loop = null)
    {
        return new Client($this->getConfig('realm'), $this->getRegisteredProviders(), $loop);
    }

    /**
     * Get a new transport provider instance.
     *
     * @return \Thruway\Transport\PawlTransportProvider
     */
    public function newTransportProvider()
    {
        return new PawlTransportProvider($this->transportUrl());
    }

    /**
     * Get a new wampcra authenticator instance.
     * 
     * @param string $authid
     * @param string $secret
     * 
     * @return \Thruway\Authentication\ClientWampCraAuthenticator
     */
    public function newAthenticator($authid, $secret)
    {
        return new ClientWampCraAuthenticator($authid, $secret);
    }

    /**
     * Get the transport URL that provider should connect to.
     *
     * @return string
     */
    public function transportUrl()
    {
        $proto = $this->getConfig('tls') ? 'wss' : 'ws';
        $port = intval($this->getConfig('port'));
        if($port>0) {
            $port = ':'.$port;
        }
        else {
            $port = '';
        }
        return $proto.'://'.$this->getConfig('host').$port.$this->getConfig('path');
    }

    /**
     * Get all configuration params or specify a given option.
     *
     * @param string $option
     *
     * @return mixed
     */
    public function getConfig($option = null)
    {
        if (isset($option)) {
            return (isset($this->config[$option])) ? $this->config[$option] : null;
        }

        return $this->config;
    }

    /**
     * Merge the current configuration with the given options.
     *
     * @param array $options
     */
    public function mergeConfig(array $options)
    {
        $this->config = array_merge($this->config, $options);
    }

    /**
     * Get the ASCII drawing of Dave the minion.
     *
     * @return string
     */
    public function gimmeASCII()
    {
        return '
           ▄▄▄▄▄▄▄▄▄▄▄▄▄
        ▄▀▀═════════════▀▀▄
       █═══════════════════█
      █═════════════════════█
     █═══▄▄▄▄▄▄▄═══▄▄▄▄▄▄▄═══█
    █═══█████████═█████████═══█
    █══██▀    ▀█████▀    ▀██══█
   ██████  █▀█  ███  █▀█  ██████
   ██████  ▀▀▀  ███  ▀▀▀  ██████
    █══▀█▄    ▄██ ██▄    ▄█▀══█
    █════▀█████▀   ▀█████▀════█
    █═════════════════════════█
    █═════════════════════════█
    █═══════▀▄▄▄▄▄▄▄▄▄▀═══════█
    █═════════════════════════█
   ▐▓▓▌═════════════════════▐▓▓▌
   ▐▐▓▓▌▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▄▐▓▓▌▌
   █══▐▓▄▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▄▓▌══█
  █══▌═▐▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▌═▐══█
  █══█═▐▓▓▓▓▓▓▄▄▄▄▄▄▄▓▓▓▓▓▓▌═█══█
  █══█═▐▓▓▓▓▓▓▐██▀██▌▓▓▓▓▓▓▌═█══█
  █══█═▐▓▓▓▓▓▓▓▀▀▀▀▀▓▓▓▓▓▓▓▌═█══█
  █══█▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓█══█
 ▄█══█▐▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▌█══█▄
 █████▐▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▌ █████
 ██████▐▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▌ ██████
  ▀█▀█  ▐▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▌   █▀█▀
         ▐▓▓▓▓▓▓▌▐▓▓▓▓▓▓▌
          ▐▓▓▓▓▌  ▐▓▓▓▓▌
         ▄████▀    ▀████▄
         ▀▀▀▀        ▀▀▀▀';
    }
}
