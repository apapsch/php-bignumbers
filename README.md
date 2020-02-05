php-bignumbers 
==============

This is a fork of the original [php-bignumbers](https://github.com/Litipk/php-bignumbers),
which does not seem to be maintained anymore. It can be used as drop-in replacement.
Motivations of the fork are:

* Create proper numbers from float regardless of the locale. Some locales use comma
  as decimal point and php-bignumbers relies on PHP string conversion of floats.
* Round correctly. There are slight rounding inaccuracies in the original package.
* Adapt the interface of [php-decimal](https://php-decimal.io/). This also solves
  the rounding issue. This is still experimental in the `devel` branch.
  
The supported PHP version is 7.x. Once the adapter is merged, the supported version
will be >=7.3.

## Getting started

You can install this library using [Composer](http://getcomposer.org/).

To install it via Composer, just write in the require block of your
composer.json file the following text:

```json
{
    "require": {
        "apapsch/php-bignumbers": "~0.9"
    }
}
```

## Learn more

See [upstream wiki](https://github.com/Litipk/php-bignumbers/wiki) for useful information. 

## How to contribute

Please send [issues](https://github.com/apapsch/php-bignumbers/issues) or
[pull requests](https://github.com/apapsch/php-bignumbers/pulls) via Github.
I don't have much plans for this package beyond the original motivations.
Since the interface of `Litipk\BigNumbers\Decimal` is already quite big,
you should not add any new methods there. Instead, create new classes or,
if it's a bigger thing, create a new Composer package.

## License

Litipk\BigNumbers is licensed under the [MIT License](https://github.com/apapsch/php-bignumbers/blob/master/LICENSE).
