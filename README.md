# [<img src="https://ipinfo.io/static/ipinfo-small.svg" alt="IPinfo" width="24"/>](https://ipinfo.io/) IPinfo Laravel Client Library

This is the official Laravel client library for the [IPinfo.io](https://ipinfo.io) IP address API, allowing you to lookup your own IP address, or get any of the following details for an IP:
 - [IP geolocation](https://ipinfo.io/ip-geolocation-api) (city, region, country, postal code, latitude and longitude)
 - [ASN details](https://ipinfo.io/asn-api) (ISP or network operator, associated domain name, and type, such as business, hosting or company)
 - [Company information](https://ipinfo.io/ip-company-api) (the name and domain of the business that uses the IP address)
 - [Carrier details](https://ipinfo.io/ip-carrier-api) (the name of the mobile carrier and MNC and MCC for that carrier if the IP is used exclusively for mobile traffic)

Check all the data we have for your IP address [here](https://ipinfo.io/what-is-my-ip).

### Getting Started

You'll need an IPinfo API access token, which you can get by singing up for a free account at [https://ipinfo.io/signup](https://ipinfo.io/signup?ref=lib-Laravel).

The free plan is limited to 50,000 requests per month, and doesn't include some of the data fields such as IP type and company data. To enable all the data fields and additional request volumes see [https://ipinfo.io/pricing](https://ipinfo.io/pricing).

#### Installation

```
composer require ipinfo/ipinfolaravel
```

Open your application's `\app\Http\Kernel.php` file and add the following to the `Kernel::middleware` property:

```php
protected $middleware = [
    ...
    \ipinfo\ipinfolaravel\ipinfolaravel::class,
];
```

#### Quick Start

```php
Route::get('/', function (Request $request) {
    $location_text = "The IP address {$request->ipinfo->ip}.";
    return view('index', ['location' => $location_text]);
});
```

will return the following string to the `index` view:

```
"The IP address 127.0.0.1."
```

### Authentication

The IPinfo library can be authenticated with your IPinfo API token. It also works without an authentication token, but in a more limited capacity. To set your access token, add the following to your app's `\config\services.php` file and replace `{{access_token}}` with your own token:

```php
'ipinfo' => [
    'access_token' => {{access_token}},
],
```

To do this in a more secure manner and avoid putting secret keys in your codebase, create an `IPINFO_SECRET` (or similar) environment variable and access this value from within `\config\services.php`, like so:

```php
'ipinfo' => [
    'access_token' => env('IPINFO_SECRET'),
],
```

### Details Data

`$request->ipinfo` is a `Details` object that contains all fields listed [IPinfo developer docs](https://ipinfo.io/developers/responses#full-response) with a few minor additions. Properties can be accessed directly.

```php
>>> $request->ipinfo->hostname
cpe-104-175-221-247.socal.res.rr.com
```

#### Country Name

`$request->ipinfo->country_name` will return the country name, as supplied by the `countries.json` file. See below for instructions on changing that file for use with non-English languages. `$request->ipinfo->country` will still return the country code.

```php
>>> $request->ipinfo->country
US
>>> $request->ipinfo->country_name
United States
```

#### Accessing all properties

`$request->ipinfo->all` will return all details data as an array.

```php
>>> $request->ipinfo->all
{
'asn': {  'asn': 'AS20001',
           'domain': 'twcable.com',
           'name': 'Time Warner Cable Internet LLC',
           'route': '104.172.0.0/14',
           'type': 'isp'},
'city': 'Los Angeles',
'company': {   'domain': 'twcable.com',
               'name': 'Time Warner Cable Internet LLC',
               'type': 'isp'},
'country': 'US',
'country_name': 'United States',
'hostname': 'cpe-104-175-221-247.socal.res.rr.com',
'ip': '104.175.221.247',
'ip_address': IPv4Address('104.175.221.247'),
'loc': '34.0293,-118.3570',
'latitude': '34.0293',
'longitude': '-118.3570',
'phone': '323',
'postal': '90016',
'region': 'California'
}
```

### Caching

In-memory caching of `Details` data is provided by default via Laravel's file-based cache. LRU (least recently used) cache-invalidation functionality has been added to the default TTL (time to live). This means that values will be cached for the specified duration; if the cache's max size is reached, cache values will be invalidated as necessary, starting with the oldest cached value.

#### Modifying cache options

Default cache TTL and maximum size can be changed by setting values in the `$settings` argument array.

* Default maximum cache size: 4096 (multiples of 2 are recommended to increase efficiency)
* Default TTL: 24 hours (in minutes)

```php
'ipinfo' => [
    'cache_maxsize' => {{cache_maxsize}},
    'cache_ttl' => {{cache_ttl}},
],
```

#### Using a different cache

It is possible to use a custom cache by creating a child class of the [CacheInterface](https://github.com/ipinfo/php/blob/master/src/cache/CacheInterface.php) class and setting the the `cache` config value in `\config\services.php`. FYI this is known as [the Strategy Pattern](https://sourcemaking.com/design_patterns/strategy).

```php
'ipinfo' => [
    ...
    'cache' => new MyCustomCacheObject(),
],
```

### IP Selection Mechanism

By default, the IP from the incoming request is used.

Since the desired IP by your system may be in other locations, the IP selection mechanism is configurable and some alternative built-in options are available.

#### Using built-in ip selectors

##### DefaultIPSelector

A [DefaultIPSelector](https://github.com/ipinfo/php/blob/master/src/iphandler/DefaultIPSelector.php) is used by default if no IP selector is provided. It returns the source IP from the incoming request.

This selector can be set explicitly by setting the `ip_selector` config value in `\config\services.php`.

```php
'ipinfo' => [
    'ip_selector' => new DefaultIPSelector(),
],
```

##### OriginatingIPSelector

A [OriginatingIPSelector](https://github.com/ipinfo/php/blob/master/src/iphandler/OriginatingIPSelector.php) selects an IP address by trying to extract it from the `X-Forwarded-For` header. This is not always the most reliable unless your proxy setup allows you to trust it. It will default to the source IP on the request if the header doesn't exist.

This selector can be set by setting the `ip_selector` config value in `\config\services.php`.

```php
'ipinfo' => [
    'ip_selector' => new OriginatingIPSelector(),
],
```

#### Using a custom IP selector

In case a custom IP selector is required, you may implement the [IPHandlerInterface](https://github.com/ipinfo/php/blob/master/src/iphandler/IPHandlerInterface.php) interface and set the `ip_selector` config value in `\config\services.php`.

For example:

```php
'ipinfo' => [
    ...
    'ip_selector' => new CustomIPSelector(),
],
```

### Internationalization

When looking up an IP address, the response object includes a `$request->ipinfo->country_name` property which includes the country name based on American English. It is possible to return the country name in other languages by telling the library to read from a custom file. To define a custom file, add the following to your app's `\config\services.php` file and replace `{{countries}}` with your own file path.

```php
'ipinfo' => [
    ...
    'countries_file' => {{countries}},
],
```

The file must be a `.json` file with the following structure:

```
{
    {{country_code}}: {{country_name}},
    "BD": "Bangladesh",
    "BE": "Belgium",
    "BF": "Burkina Faso",
    "BG": "Bulgaria"
    ...
}
```

### Filtering

By default, `ipinfolaravel` filters out requests that have `bot` or `spider` in the user-agent. Instead of looking up IP address data for these requests, the `$request->ipinfo` attribute is set to `null`. This is to prevent you from unnecessarily using up requests on non-user traffic. This behavior can be switched off by adding the following to your app's `\config\services.php` file.

```php
'ipinfo' => [
    ...
    'filter' => false,
 ],
```

To set your own filtering rules, *thereby replacing the default filter*, you can set `ipinfo.config` to your own, custom callable function which satisfies the following rules:

* Accepts one request.
* Returns *True to filter out, False to allow lookup*

To use your own filter function:

```php
'ipinfo' => [
    ...
    'filter' => $customFilterFunction,
],
```

### Suppressing Exceptions

Laravel middleware does not allow you to catch exceptions from other
middleware, so if the IPinfo middleware throws an exception, it'll be quite
hard to deal with it.

We allow suppressing exceptions by specifying the `no_except` key in the
config:

```php
'ipinfo' => [
    ...
    'no_except' => true,
],
```

If an exception occurs when this setting is `true`, the `$request->ipinfo`
object will be equal to `null`.

### Other Libraries

There are official IPinfo client libraries available for many languages including PHP, Python, Go, Java, Ruby, and many popular frameworks such as Django, Rails and Laravel. There are also many third party libraries and integrations available for our API.

### About IPinfo

Founded in 2013, IPinfo prides itself on being the most reliable, accurate, and in-depth source of IP address data available anywhere. We process terabytes of data to produce our custom IP geolocation, company, carrier, VPN detection, hosted domains, and IP type data sets. Our API handles over 40 billion requests a month for 100,000 businesses and developers.

[![image](https://avatars3.githubusercontent.com/u/15721521?s=128&u=7bb7dde5c4991335fb234e68a30971944abc6bf3&v=4)](https://ipinfo.io/)
