[![Build Status](https://travis-ci.org/Vinelab/minion.svg?branch=master)](https://travis-ci.org/Vinelab/minion)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/aefa4fa3-e213-4a81-873c-94277c05543a/big.png)](https://insight.sensiolabs.com/projects/aefa4fa3-e213-4a81-873c-94277c05543a)

# Minion
A simplified client the WAMP v2 protocol (Web Application Messaging Protocol) with a handy command line tool - PHP WebSocket made easy.

Based on the great work put together by [Thruway](http://github.com/voryx/Thruway), Minion will give you the simplicity
and flexibility of running `minion run` and get a client running in no time. In addition to helping you structure
your application. See [How It Works](#how-it-works) for details.

For a jump-start head over to the [Quick Start Guide](https://github.com/Vinelab/minion/wiki/Quick-Start-with-Crossbar.io) or read on for detailed docs.
Or you may take a look at the [Examples](https://github.com/Vinelab/minion/tree/master/Examples) to get an idea about how this works.

## Installation

### Composer
Add the following to `require` in `composer.json`
```json
"vinelab/minion": "*"
```
Then run `composer update` to install.

#### Laravel Bounties
* Add `Vinelab\Minion\MinionServiceProvider` to the `providers` array in your `app.php` and you'll have a `Minion`
facade available throught your project.
* The command line tool is available through artisan as `php artisan minion:run` see [CLI](#cli)
* Run `php artisan vendor:publish` and head to `app/config/minion.php` to configure your minion.

## Configuration
Configure the connection parameters you want your client to use when it connects to a WAMP router.

### Router

```php
$m = new Minion();
$m->run(['realm' => 'myrealm', 'host' => 'some.host.ws', 'port' => 8182]);
```

### Authentication
A basic wampcra authenticator for minion can be enabled by adding the configuration for authentication.

```php
$m = new Minion();
$m->run([
    'realm' => 'secretrealm',
    'auth' => [
        'authid' => 'minion',
        'secret' => 'ultrasecretkey'
    ]
]);
```

### Provider Registration

```php
$m = new Minion();
$m->register('ChatProvider');
$m->register('MyApp\Providers\NotificationProvider'):
$m->run();
```

You may also find it useful to list the providers in the config as such:

```php
$m = new Minion();
$m->run(
    [
        'port'     => 9876,

        'host'     => 'the.host',

        'realm'    => 'somerealm',

        'register' => [
            'ChatProvider',
            'SomeOtherProvider'
            'NotificationProvider',
        ]
    ]
);
```

### Loop
In existing applications it may be useful to be re-use an existing ReactPHP loop. You can pass in a LoopInterface like so:

```php
$loop = React\EventLoop\Factory::create();
$m = new Minion();
$m->run([], $loop);
```

## Usage
The idea behind Minion is to help structure your application and get it ready for scale
with real-time communication by using **providers** to *register RPCs* and *publish and subscribe to topics*
with predefined functionalities to make things quick. For more about RPCs and Pub/Sub see [Introduction to WAMP programming](http://autobahn.ws/js/tutorial.html)

### How It Works
WAMP is a protocol that defines a Router that handles connections of clients, your application is one
of these clients and the application logic is implemented within *providers* which you can
*register* with Minion using the `register($provider)` method. A provider can be the name of a class
(full namespace if applicable) or a `Closure`.

Consider the following directory structure:

```
src/
vendor/
start.php
composer.json
```

### Provider Classes
* Provider classes is where your application logic resides, Minion uses topic prefixes as a convention to distinguish
providers and that is done by specifying a `protected $prefix = 'topic.prefix.';` in your provider class.
> It is a convention to use dot '.' separated prefixes such as `chat.` which will result in topic `read` end up being `chat.read`
* Every provider class **must extend** `Vinelab\Minion\Provider` and implement `public function boot()` method
which is the best place to have your registrations and pub/sub operations.
* Every method **registered** or **subscribed** will receive the `$args` and `$data` when involved.
    **Consider this method**
    ```php
    public function get($args, $data)
    ```
    * `$args` is the array of the args passed from the call
    * `$data` is a `Dictionary` instance where you can safely access attributes like `$data->something` and when they
    don't exist you get a `null` value instead of an error as in `StdClass` objects, though you may use the `$data`
    variable as you would use any other object with `isset($data->prop)` and `empty($data->prop)`

* `src/ChatProvider.php`
```php
<?php

use Vinelab\Minion\Provider;

class ChatProvider extends Provider
{
    protected $prefix = 'chat.';

    public function boot()
    {
        // will be registered to topic: chat.send
        $this->register('send', 'sendMessage');
    }

    public function sendMessage($args, $data)
    {
        $message = $data->message;

        // store message in the database

        // tell everyone about it
        $this->publish('message', compact('message'));

        // response with the status
        return true;
    }
}
```
* `start.php`


```php
use Vinelab\Minion\Minion;

$m = new Minion;
$m->register('ChatProvider');
$m->run();
```

### Closures as Providers
* `start.php`

```php
require __DIR__.'/vendor/autoload.php'

use Vinelab\Minion\Minion;
use Vinelab\Minion\Client;

// Get a minion instance
$m = new Minion;

$add = function ($x, $y) { return $x + $y; };

// Register a closure provider
$m->register(function (Client $client) use ($add) {

    // register
    $client->register('add', $add);

    // subscribe
    $client->subscribe('some.topic', function ($data) {
        // do things with data
        $data->key;
        $data->other_key;
    });

    // publish
    $client->publish('i.am.here', ['name' => 'mr.minion']);
});
```

### CLI
Minion comes with a handy command line tool for usage straight from the command line. Once you install using composer
a `minion` binary will be in your `vendor/bin/`. To make things easier you can run `export PATH="./vendor/bin:$PATH"`
to use `minion run` straight instead of `./vendor/bin/minion run`

use `minion list` for a list of available commands and `minion --help [command]` for more info about each of them.

##### Commands
* `run`
    * **Options**
        * `--realm`: Specify WAMP realm to be used
        * `--host`: Specify the router host
        * `--port`: Specify the router port
        * `--register`: Register provider classes (can be used multiple times)
    * **Example**
    `minion run --realm=chatting --port=9876 --register="ChatProvider" --register="MyApp\Providers\NotificationsProvider"`

### Crossbar.io
Minion ships with a minimal [crossbar.io](http://crossbar.io) config file which you can find at
`./vendor/vinelab/minion/.crossbar/config.json` and to start crossbar using it run
`crossbar start --cbdir ./vendor/vinelab/minion/.crossbar`

To get started with Crossbar visit the [Quick Start with Crossbar.io Guide](https://github.com/Vinelab/minion/wiki/Quick-Start-with-Crossbar.io).

For more information about crossbar head over to [Crossbar.io Quick Start](https://github.com/crossbario/crossbar/wiki/Quick-Start).

## Contributing
Pull Requests are most welcome! Dev packages are specified in `composer.json` under `require-dev`

* Run tests with `./vendor/bin/phpunit`
* Coding standards must be checked before committing code by issuing: `./vendor/bin/phpcs --standard=phpcs.xml src`
* In the case of violations use `./vendor/bin/php-cs-fixer fix src` which will help solve them out

## License
Minion is distributed under the MIT License, see the LICENSE file in this package.
