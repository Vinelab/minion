<?php namespace Vinelab\Minion;

/**
 * @author Abed Halawi <abed.halawi@vinelab.com>
 */
 
 // Provider's namespace is Vinelab\Minion\Provider
class DummyProvider extends Provider {

    protected $prefix = 'com.example.';

    public function boot()
    {
        $this->register('getphpversion', 'getPhpVersion');
        $this->subscribe('pripra', 'papa');
    }

    public function getPhpVersion()
    {
        $this->publish('asked', null, ['send' => 'thing']);

        return phpversion();
    }

    public function papa()
    {
        var_dump('PAPA CALLED!');
    }
}
