<?php

namespace Vinelab\Minion;

use Closure;
use Psr\Log\NullLogger;
use Thruway\Logging\Logger;

/**
 * @author Abed Halawi <abed.halawi@vinelab.com>
 */
class Client extends \Thruway\Peer\Client
{
    /**
     * The prefix to use when generating topics/.
     *
     * @var string
     */
    protected $topicPrefix = '';

    /**
     * The delegate provider instance.
     *
     * @var \Vinelab\Minion\Provider
     */
    protected $delegateProvider;

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
     */
    public function onSessionStart($session, $transport)
    {
        // Below disabled. Logging is now handled through a static class
        // so there is no way that I can see to disable the logging since
        // the split from voryx/thruway to thruway/client. getManager is
        // also no longer a method on the Client class.
        // Shush the logs.
        // $this->getManager()->setQuiet(true);
        
        // Boot up providers
        $this->bootProviders();
    }

    /**
     * Start the transport.
     *
     * @param bool $startLoop
     *
     * @throws \Exception
     */
    public function start($debug = false, $startLoop = true)
    {
        if (!$debug) {
            Logger::set(new NullLogger());
        }

        parent::start($startLoop);
    }

    /**
     * Boot up the registered providers by calling their boot() method.
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
     * Get the registered providers.
     *
     * @return array
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * Call a registered procedure.
     *
     * @param string $procedure
     * @param array  $arguments
     * @param array  $argumentsKw
     * @param array  $options
     *
     * @return \React\Promise\Promise
     */
    public function call($procedure, $arguments = null, $argumentsKw = null, $options = null)
    {
        return $this->getSession()->call($this->prepareTopic($procedure), $arguments, $argumentsKw, $options);
    }

    /**
     * Subscribe to a topic and specify a callback for it.
     *
     * @param string         $topic      The topic name.
     * @param string|Closure $callback   The callable to be called when the topic received a publish.
     * @param array          $options    Will be passed straight to thruway @see \Thruway\ClientSession::subscribe
     * @param bool           $isFunction Specify whether you're passing in a function from a different scope,
     *                                   Set this to TRUE to avoid calling the passed callable from this provider's
     *                                   scope.
     *
     * @return \React\Promise\Promise
     *
     * @see \Thruway\ClientSession::subscribe
     */
    public function subscribe($topic, $callback, $options = null, $isFunction = false)
    {
        return $this->getSession()->subscribe(
            $this->prepareTopic($topic),
            $this->wrapWithProxy($callback, $isFunction),
            $options
        );
    }

    /**
     * Publish to a topic with the given data.
     *
     * @param string      $topic
     * @param array|mixed $arguments
     * @param array|mixed $argumentsKw
     * @param array       $options
     *
     * @return \React\Promise\Promise
     */
    public function publish($topic, $arguments = null, $argumentsKw = null, $options = null)
    {
        return $this->getSession()->publish($this->prepareTopic($topic), $arguments, $argumentsKw, $options);
    }

    /**
     * Register a RPC.
     *
     * @param string  $topic
     * @param Closure $callback
     * @param array   $options
     * @param bool    $isFunction
     *
     * @return \React\Promise\Promise
     */
    public function register($topic, $callback, $options = null, $isFunction = false)
    {
        return $this->getCallee()->register(
            $this->getSession(),
            $this->prepareTopic($topic),
            $this->wrapWithProxy($callback, $isFunction),
            $options
        );
    }

    /**
     * Prepare the topic by prefixing it with @property $prefix
     *  as a convenience for having to manually prefix every topic.
     *
     * @param string $topic
     *
     * @return string
     */
    protected function prepareTopic($topic)
    {
        return $this->getTopicPrefix().$topic;
    }

    /**
     * Set the topic prefix.
     *
     * @param string $prefix
     */
    public function setTopicPrefix($prefix)
    {
        $this->topicPrefix = $prefix;
    }

    /**
     * Get the topic prefix.
     *
     * @return string
     */
    public function getTopicPrefix()
    {
        return $this->topicPrefix;
    }

    /**
     * Set the delegate provider of this client.
     *
     * @param \Vinelab\Minion\Provider $provider
     */
    public function setDelegateProvider(Provider $provider)
    {
        $this->delegateProvider = $provider;
    }

    /**
     * Get the delegate provider of this client.
     *
     * @return \Vinelab\Minion\Provider
     */
    public function getDelegateProvider()
    {
        return $this->delegateProvider;
    }

    /**
     * Wrap the given callback with a proxy Closure.
     * The reason we use this is to be able to format the given $data into a Dictionary
     * which makes it safer to work with them.
     *
     * @param mixed $callback
     * @param bool  $isFunction
     *
     * @return Closure
     */
    public function wrapWithProxy($callback, $isFunction = false)
    {
        // Save provider context for resolving method by name with many providers use
        $provider = $this->getDelegateProvider();

        // We will wrap the callback with a Closure so that we can format the kwArgs that we receive
        // into our proprietary Dictionary instance to make things safer.
        return function ($args, $kwArgs) use ($callback, $isFunction, $provider) {

            if (is_string($callback) && !$isFunction && $provider instanceof Provider) {
                $callback = [$provider, $callback];
            }

            return call_user_func_array($callback, [$args, Dictionary::make($kwArgs)]);
        };
    }
}
