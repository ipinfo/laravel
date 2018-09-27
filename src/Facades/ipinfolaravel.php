<?php

namespace ipinfo\ipinfolaravel\Facades;

use Illuminate\Support\Facades\Facade;

class ipinfolaravel extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'ipinfolaravel';
    }
}
