<?php

namespace ipinfo\ipinfolaravel;

use Closure;
use ipinfo\ipinfo\IPinfo as IPinfoClient;
use ipinfo\ipinfolaravel\DefaultCache;

class ipinfolaravel
{
    /**
     * IPinfo API access token.
     * @var string
     */
    public $access_token = null;

    /**
     * IPinfo client object settings.
     * @var array
     */
    public $settings = [];

    /**
     * Return true to skip IPinfo lookup, otherwise return false.
     * @var function
     */
    public $filter = null;

    const CACHE_MAXSIZE = 4096;
    const CACHE_TTL = 60 * 24;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->configure();

        if ($this->filter && call_user_func($this->filter, $request)) {
          $details = null;
        } else {
          $ipinfo = new IPinfoClient($this->access_token, $this->settings);
          $details = $ipinfo->getDetails();
        }

        $request->merge(['ipinfo' => $details]);

        return $next($request);
    }

  /**
   * Determine settings based on user-defined configs or use defaults.
   */
    public function configure()
    {
      $this->access_token = config('services.ipinfo.access_token', null);
      $this->filter = config('services.ipinfo.filter', [$this, 'defaultFilter']);

      if ($custom_countries = config('services.ipinfo.countries_file', null)) {
        $this->settings['countries_file'] = $custom_countries;
      }

      if ($custom_cache = config('services.ipinfo.cache', null)) {
        $this->settings['cache'] = $custom_cache;
      } else {
        $maxsize = config('services.ipinfo.cache_maxsize', self::CACHE_MAXSIZE);
        $ttl = config('services.ipinfo.cache_ttl', self::CACHE_TTL);
        $this->settings['cache'] = new DefaultCache($maxsize, $ttl);
      }
    }

    /**
     * Should IP lookup be skipped.
     * @param  Request $request Request object.
     * @return bool          Whether or not to filter out.
     */
    public function defaultFilter($request)
    {
        $user_agent = $request->header('user-agent');
        if ($user_agent) {
          $lower_user_agent = strtolower($user_agent);

          $is_spider = strpos($lower_user_agent, 'spider') !== false;
          $is_bot = strpos($lower_user_agent, 'bot') !== false;

          return $is_spider || $is_bot;
        }

        return false;
    }
}
