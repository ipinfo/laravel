<?php

namespace ipinfo\ipinfolaravel\iphandler;

/**
 * Default implementation of the IPHandlerInterface. Selects default ip from request.
 */
class DefaultIPHandler implements IPHandlerInterface
{

    /**
     * Selectes default IP address from request.
     * @param \Illuminate\Http\Request $request
     * @return string IP address.
     */
    public function getIP($request)
    {
        return $request->ip();
    }
}
