<?php

namespace ipinfo\ipinfolaravel\iphandler;

/**
 * Selects originating client IP from the request.
 */
class OriginatingIPSelector implements IPHandlerInterface
{

    /**
     * Selects originating client IP from request.
     * @param \Illuminate\Http\Request $request
     * @return string IP address.
     */
    public function getIP($request)
    {
        $xForwardedFor = $request->headers->get('x-forwarded-for');
        if (empty($xForwardedFor)) {
            $ip = $request->ip();
        } else {
            $ips = explode(',', $xForwardedFor);
            // trim as officially the space comes after each comma separator
            $ip = trim($ips[0]);
        }
        return $ip;
    }
}
