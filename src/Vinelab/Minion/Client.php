<?php namespace Vinelab\Minion;

use Closure;

/**
 * @author Abed Halawi <abed.halawi@vinelab.com>
 */
class Client extends \Thruway\Peer\Client {

    /**
     * The registered providers.
     *
     * @var array
     */
    private $providers = [];

    /**
     * Constructor.
     *
     * @param string $realm
     * @param array  $providers
     */
    public function __construct($realm, array $providers)
    {
        parent::__construct($realm);

        $this->providers = $providers;
    }

    /**
     * Called when the server session has started which will
     * call all the providers so that they perform whatever they have
     * to do.
     *
     * @param \Thruway\AbstractSession              $session
     * @param \Thruway\Transport\TransportInterface $transport
     *
     * @return void
     */
    public function onSessionStart($session, $transport)
    {
        // Shush the logs.
        $this->getManager()->setQuiet(true);
        // Boot up providers
        $this->bootProviders();
    }

    /**
     * Boot up the registered providers by calling their boot() method.
     *
     * @return void
     */
    private function bootProviders()
    {
        foreach ($this->providers as $provider) {
            if ($provider instanceof Closure) {
                $provider($this);
            } else {
                (new $provider($this))->boot();
            }
        }
    }

    /**
     * Start the transport
     *
     * @return void
     */
    public function start()
    {
    }
}
