<?php

namespace ipinfo\ipinfolaravel\iphandler;

/**
 * Interface for handling the mechanism for IP retrieval.
 */
interface IPHandlerInterface
{

  /**
   * Get IP address.
   * @return string IP address.
   */
    public function getIP();
}
