<?php

namespace ipinfo\ipinfolaravel;

use Illuminate\Support\Facades\Cache;
use ipinfo\ipinfo\cache\CacheInterface;


/**
 * Default implementation of the CacheInterface. Provides in-memory caching.
 */
class DefaultCache implements CacheInterface {

  public $maxsize;
  public $ttl;

  private $element_queue;

  public function __construct(int $maxsize, int $ttl)
  {
    $this->element_queue;
    $this->maxsize = $maxsize;
    $this->ttl = $ttl;
  }

  /**
   * Tests if the specified IP address is cached.
   * @param  string  $ip_address IP address to lookup.
   * @return boolean Is the IP address data in the cache.
   */
  public function has(string $name)
  {
    return Cache::has($name);
  }

  /**
   * Set the IP address key to the specified value.
   * @param string $ip_address IP address to cache data for.
   * @param mixed $value Data for specified IP address.
   */
  public function set(string $name, $value)
  {
      if (!$this->has($name)) {
        $this->element_queue[] = $name;
      }

      Cache::put($name, $value, $this->ttl);

      $this->manageSize();
  }

  /**
   * Get data for the specied IP address.
   * @param  string $ip_address IP address to lookup in cache.
   * @return mixed IP address data.
   */
  public function get(string $name)
  {
    return Cache::get($name);
  }

  /**
   * If cache maxsize has been reached, remove oldest elements until limit is reached.
   */
  private function manageSize()
  {
    $overflow = count($this->element_queue) - $this->maxsize;
    if ($overflow > 0) {
        foreach (array_slice($this->element_queue, 0, $overflow) as $name) {
          if ($this->has($name)) {
            Cache::forget($name);
          }
        }
        $this->element_queue = array_slice($this->element_queue, $overflow);
    }
  }
}
