<?php

namespace ipinfo\ipinfolaravel\iphandler;

/**
 * Implementation of the IPHandlerInterface used as default option. Retrieve IP from request.
 */
class DefaultIPSelector implements IPHandlerInterface
{

    /**
     * Selects default IP address from request.
     * @param \Illuminate\Http\Request $request
     * @return string IP address.
     */
    public function getIP($request)
    {
        return $request->ip();
    }
}
