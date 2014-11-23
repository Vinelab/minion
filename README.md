[![Build Status](https://travis-ci.org/Vinelab/minion.svg?branch=master)](https://travis-ci.org/Vinelab/minion)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/aefa4fa3-e213-4a81-873c-94277c05543a/big.png)](https://insight.sensiolabs.com/projects/aefa4fa3-e213-4a81-873c-94277c05543a)

# Minion
Simple PHP Websocket router implementing [WAMP v2](http://wamp.ws) based on [Thruway](http://github.com/voryx/thruway)

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
* Run `php artisan config:publish vinelab/minion` and head to `app/config/packages/vinelab/minion/minion.php` to configure your minion.


## Usage
The idea behind Minion is to help structure your application for real-time communication by using **providers**
to *register RPCs* and *publish and subscribe to topics* with predefined functionalities to make things quick.

### How It Works
WAMP is a protocol that defines a router that handles connections of clients, your application is one
of these clients called *Internal Client* and the application logic is implemented using *providers* which you can
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
        $message = $data['message'];
        // store message in the database
        // ...
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

## Contributing
Pull Requests are most welcome! Dev packages are specified in `composer.json` under `require-dev`

* Run tests with `./vendor/bin/phpunit`
* Coding standards must be checked before committing code by issuing: `./vendor/bin/phpcs --standard=phpcs.xml src`
* In the case of violations use `./vendor/bin/php-cs-fixer fix src` which will help solve them out

## License
Minion is distributed under the MIT License, see the LICENSE file in this package.
