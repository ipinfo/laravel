The official Laravel library for the `IPinfo <https://ipinfo.io/>`_ API.
###########################################################################

A lightweight wrapper for the IPinfo API, which provides up-to-date IP address data.

.. contents::

.. section-numbering::


Installation
=====

>>> composer require ipinfo/ipinfolaravel

Open your application's ``\app\Http\Kernel.php`` file and add the following to the ``Kernel::middleware`` property::

  protected $middleware = [
  ...
  \ipinfo\ipinfolaravel\ipinfolaravel::class,
  ];


Usage
=====
Once configured, ``ipinfolaravel`` will make IP address data accessible within Laravel's Request object under the ``ipinfo`` property. The following view from the ``web.php`` file which defines routes::

  Route::get('/', function (Request $request) {
      $location_text = "The IP address {$request->ipinfo->ip} is located in the city of {$request->ipinfo->city}."

      return view('index', ['location' => $location_text]);
  });

will return the following string to the ``index`` view::

  "The IP address 216.239.36.21 is located in the city of Emeryville."

Authentication
==============
The IPinfo library can be authenticated with your IPinfo API token. It also works without an authentication token, but in a more limited capacity. To set your access token, add the following to your app's ``\config\services.php`` file and replace ``{{access_token}}`` with your own token:: 


  'ipinfo' => [
        'access_token' => {{access_token}},
    ],

To do this in a more secure manner and avoid putting secret keys in your codebase, create an ``IPINFO_SECRET`` (or similar) environment variable and access this value from within ``\config\services.php``, like so::

  'ipinfo' => [
        'access_token' => env('IPINFO_SECRET'),
    ],


Details Data
=============
`$request->ipinfo` is a `Details` object that contains all fields listed `IPinfo developer docs <https://ipinfo.io/developers/responses#full-response>`_ with a few minor additions. Properties can be accessed directly.

>>> $request->ipinfo->hostname
cpe-104-175-221-247.socal.res.rr.com


Country Name
------------

`$request->ipinfo->country_name` will return the country name, as supplied by the `countries.json` file. See below for instructions on changing that file for use with non-English languages. `$request->ipinfo->country` will still return the country code.

>>> $request->ipinfo->country
US
>>> $request->ipinfo->country_name
United States

Accessing all properties
------------------------

`$request->ipinfo->all` will return all details data as an array.

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

Caching
=======
By default, in-memory caching is not provided as part of the ``ipinfolaravel`` library because HTTP requests are stateless, so this would not be possible. However, it is possible to use a custom cache by creating a child class of the `CacheInterface <https://github.com/ipinfo/php/blob/master/src/cache/Interface.php>`_ class and setting the the ``cache`` config value in ``\config\services.php``. FYI this is known as `the Strategy Pattern <https://sourcemaking.com/design_patterns/strategy>`_.
::

  'ipinfo' => [
        ...
        'cache' => new MyCustomCacheObject(),
    ],

Internationalization
====================
When looking up an IP address, the response object includes a ``$request->ipinfo->country_name`` property which includes the country name based on American English. It is possible to return the country name in other languages by telling the library to read from a custom file. To define a custom file, add the following to your app's ``\config\services.php`` file and replace ``{{countries}}`` with your own file path:: 


  'ipinfo' => [
        ...
        'countries_file' => {{countries}},
    ],

The file must be a ``.json`` file with the following structure::

    {
     {{country_code}}: {{country_name}}, 
     "BD": "Bangladesh",
     "BE": "Belgium",
     "BF": "Burkina Faso",
     "BG": "Bulgaria"
     ...
    }

Filtering
=========

By default, ``ipinfolaravel`` filters out requests that have ``bot`` or ``spider`` in the user-agent. Instead of looking up IP address data for these requests, the ``$request->ipinfo`` attribute is set to ``null``. This is to prevent you from unnecessarily using up requests on non-user traffic. This behavior can be switched off by adding the following to your app's ``\config\services.php`` file::

  'ipinfo' => [
        ...
        'filter' => false,
   ],
    
To set your own filtering rules, *thereby replacing the default filter*, you can set ``ipinfo.config`` to your own, custom callable function which satisfies the following rules:

* Accepts one request.
* Returns *True to filter out, False to allow lookup*

To use your own filter function::

  'ipinfo' => [
        ...
        'filter' => $customFilterFunction,
  ],
