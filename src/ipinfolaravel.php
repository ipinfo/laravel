<?php

namespace ipinfo\ipinfolaravel;

use Closure;
use ipinfo\ipinfo\IPinfo as IPinfoClient;

class ipinfolaravel
{
    /**
     * IPinfo API access token.
     * @var string
     */
    protected $access_token = null;

    /**
     * IPinfo client object settings.
     * @var array
     */
    protected $settings = [];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->filter($request)) {
          $details = null;
        } else {
          $ipinfo = new IPinfoClient($this->access_token, $this->settings);
          $details = $ipinfo->getDetails();
        }

        $request->merge(['ipinfo' => $details]);

        return $next($request);
    }

    /**
     * Should IP lookup be skipped.
     * @param  Request $request Request object.
     * @return bool          Whether or not to filter out.
     */
    protected function filter($request)
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
