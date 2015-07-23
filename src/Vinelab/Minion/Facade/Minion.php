<?php

namespace Vinelab\Minion\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * @author Abed Halawi <abed.halawi@vinelab.com>
 */
class Minion extends Facade
{
    /**
     * Get the accessor of this facade.
     *
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return 'vinelab.minion';
    }
}
