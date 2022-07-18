<?php

namespace ipinfo\ipinfolaravel\iphandler;

/**
 * Interface for handling the mechanism of IP retrieval.
 */
interface IPHandlerInterface
{

    /**
     * Get IP address.
     * @param \Illuminate\Http\Request $request
     * @return string IP address.
     */
    public function getIP($request);
}
