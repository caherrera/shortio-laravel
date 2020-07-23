Laravel Shortio
==============

Laravel Shortio was created by, and is maintained by [Carlos Herrera](https://github.com/caherrera), and is a [PHP API Client for Short.io](https://short.io) bridge for [Laravel](http://laravel.com). Feel free to check out the [change log](CHANGELOG.md), [releases](https://github.com/caherrera/shortio-laravel/releases), [security policy](https://github.com/caherrera/shortio-laravel/security/policy), [license](LICENSE), [code of conduct](.github/CODE_OF_CONDUCT.md), and [contribution guidelines](.github/CONTRIBUTING.md).

<p align="center">
<a href="https://github.com/caherrera/shortio-laravel/actions?query=workflow%3ATests"><img src="https://img.shields.io/github/workflow/status/caherrera/shortio-laravel/Tests?label=Tests&style=flat-square" alt="Build Status"></img></a>
<a href="https://github.styleci.io/repos/279953049?branch=master"><img src="https://github.styleci.io/repos/279953049/shield?branch=master" alt="StyleCI"></a>
<a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-brightgreen?style=flat-square" alt="Software License"></img></a>
<a href="https://packagist.org/packages/caherrera/shortio-laravel"><img alt="Packagist Downloads" src="https://img.shields.io/packagist/dt/caherrera/shortio-laravel"></a>
<a href="https://packagist.org/packages/caherrera/shortio-laravel"><img alt="Packagist Version" src="https://img.shields.io/packagist/v/caherrera/shortio-laravel"></a>
</p>


## Installation

Laravel GitLab requires [PHP](https://php.net) 7.2-7.4. This particular version supports Laravel 7.

To get the latest version, simply require the project using [Composer](https://getcomposer.org). 

```bash
$ composer require caherrera/shortio-laravel
```

Once installed, if you are not using automatic package discovery, then you need to register the `Shortio\Laravel\ShortioServiceProvider` service provider in your `config/app.php`.

You can also optionally alias our facade:

```php
        'Shortio'      => Shortio\Laravel\Facades\Shortio::class,
```


## Configuration

Laravel GitLab requires connection configuration.

To get started, you'll need to publish all vendor assets:

```bash
$ php artisan vendor:publish
```

This will create a `config/shortio.php` file in your app that you can modify to set your configuration. Also, make sure you check for changes to the original config file in this package between releases.

##### Real Examples

Here you can see an example of just how simple this package is to use. Out of the box, the default adapter is `main`. After you enter your authentication details in the config file, it will just work:

```php
use Shortio\Laravel\Facades\Shortio;
// you can alias this in config/app.php if you like

shortio::domains()->all();
// we're done here - how easy was that, it just works!
```

If you prefer to use models over facades like me, then you can easily inject the manager like so:

```php
use Shortio\Laravel\Model\Link;

class Foo
{
    protected $link;

    public function __construct()
    {
        $this->link = new Link();
    }

    public function bar()
    {
        return $this->link->all();
    }
}


```

##### Further Information

There are other classes in this package that are not documented here. This is because they are not intended for public use and are used internally by this package.

## Security

If you discover a security vulnerability within this package, please rise an issue. All security vulnerabilities will be promptly addressed. You may view our full security policy [here](https://github.com/caherrera/shortio-laravel/security/policy).


## License

Laravel GitLab is licensed under [The MIT License (MIT)](LICENSE).

## Contributions
Special thanks to 

* [Gabriel Lavini](https://github.com/glavini/)
* Francisco Molina
* Felipe Galdamez
* Juan Bardas

