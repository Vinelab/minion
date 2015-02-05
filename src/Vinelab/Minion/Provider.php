<?php namespace Vinelab\Minion;

/**
 * @author Abed Halawi <abed.halawi@vinelab.com>
 */
abstract class Provider {

    /**
     * The topic prefix.
     *
     * @var string
     */
    protected $prefix = '';

    /**
     * Constructor.
     *
     * @param \Vinelab\Minion\Client $client
     */
    public function __construct($client)
    {
        $this->setClient($client);
    }

    /**
     * Boot this provider. This is the best place to have
     * your subscriptions/registrations for your RPCs and PubSub.
     *
     * @return void
     */
    abstract public function boot();

    /**
     * Set the client instance.
     *
     * @param \Mulkave\Minion\Client $client
     *
     * @return void
     */
    private function setClient(Client $client)
    {
        $this->minionClient = $client;
    }

    /**
     * Get the client instance.
     *
     * @return \Vinelab\Minion\Client
     */
    protected function getClient()
    {
        return $this->minionClient;
    }

    /**
     * Get the client session.
     *
     * @return \Thruway\ClientSession
     */
    protected function getSession()
    {
        return $this->getClient()->getSession();
    }

    /**
     * Get callee.
     *
     * @return \Thruway\Role\Callee
     */
    protected function getCallee()
    {
        return $this->getClient()->getCallee();
    }

    /**
     * Subscribe to a topic and specify a callback for it.
     *
     * @param string         $topic      The topic name.
     * @param string|Closure $callback   The callable to be called when the topic received a publish.
     * @param array          $options    Will be passed straight to thruway @see \Thruway\ClientSession::subscribe
     * @param boolean        $isFunction Specify whether you're passing in a function from a different scope,
     *                                   Set this to TRUE to avoid calling the passed callable from this provider's
     *                                   scope.
     *
     * @return \React\Promise\Promise
     * @see \Thruway\ClientSession::subscribe
     */
    protected function subscribe($topic, $callback, $options = null, $isFunction = false)
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
    protected function publish($topic, $arguments = null, $argumentsKw = null, $options = null)
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
    protected function register($topic, $callback, $options = null, $isFunction = false)
    {
        return $this->getCallee()->register(
            $this->getSession(),
            $this->prepareTopic($topic),
            $this->wrapWithProxy($callback, $isFunction),
            $options
        );
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
    protected function call($procedure, $arguments = null, $argumentsKw = null, $options = null)
    {
        return $this->getSession()->call($this->prepareTopic($procedure), $arguments, $argumentsKw, $options);
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
     *
     * @return void
     */
    public function setTopicPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * Get the topic prefix.
     *
     * @return string
     */
    public function getTopicPrefix()
    {
        return $this->prefix;
    }

    /**
     * Wrap the given callback with a proxy Closure.
     * The reason we use this is to be able to format the given $data into a Dictionary
     * which makes it safer to work with them.
     *
     * @param  mixed $callback
     * @param  boolean $isFunction
     *
     * @return Closure
     */
    public function wrapWithProxy($callback, $isFunction = false)
    {
        // We will wrap the callback with a Closure so that we can format the kwArgs that we receive
        // into our proprietary Dictionary instance to make things safer.
        return function ($args, $kwArgs) use($callback, $isFunction) {

            if (is_string($callback) && ! $isFunction) {
                $callback = [$this, $callback];
            }

            return call_user_func_array($callback, [$args, Dictionary::make($kwArgs)]);
        };
    }
}
