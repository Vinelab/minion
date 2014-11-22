<?php namespace Vinelab\Minion;

use Closure;
use Thruway\Peer\Router;
use Thruway\Transport\RatchetTransportProvider;
use Thruway\Transport\InternalClientTransportProvider;

/**
 * @author Abed Halawi <abed.halawi@vinelab.com>
 */
class Minion {

    /**
     * Hold the registered providers.
     *
     * @var array
     */
    private $providers = [];

    /**
     * The default configuration parameters.
     *
     * @var array
     */
    private $defaultConfig = [
        'realm' => 'minion',
        'host'  => '127.0.0.1',
        'port'  => 9090,
    ];

    /**
     * The configuration of this minion.
     *
     * @var array
     */
    private $config;

    /**
     * Register the given provider.
     *
     * @param Closure|string $provider
     *
     * @return void
     */
    public function register($provider)
    {
        // When we receive a string we consider it to be a provider, if not we throw them away!
        if (is_string($provider) && ! is_a($provider, '\Vinelab\Minion\Provider', true)) {
            throw new InvalidProviderException(
                sprintf('Provider %s must be an instance of \Vinelab\Minion\Provider', $provider)
            );
        }

        if (! in_array($provider, $this->providers)) {
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
     *
     * @return void
     *
     * @throws \Exception If encountered any failure starting the server.
     */
    public function run($options = [])
    {
        // Merge the options into the configurations
        $this->mergeConfig($options);

        $router = new Router();
        $transportProvider = new RatchetTransportProvider($this->getConfig('host'), $this->getConfig('port'));
        $router->addTransportProvider($transportProvider);

        $internalTransportProvider = new InternalClientTransportProvider(
            new Client($this->getConfig('realm'), $this->getRegisteredProviders())
        );

        $router->addTransportProvider($internalTransportProvider);

        // Print out our lovely minion.
        echo $this->gimmeASCII();

        // Start the router
        $router->start();
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
     *
     * @return void
     */
    public function mergeConfig(array $options)
    {
        $this->config = array_merge($this->defaultConfig, $options);
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
