# php-system
A generic way to find informations about your system

## Installation

The installation of this library is made via composer.
Download `composer.phar` from [their website](https://getcomposer.org/download/).
Then add to your composer.json :

```json
	"require": {
		...
		"php-extended/php-system": "^1.0",
		...
	}
```

Then run `php composer.phar update` to install this library.
The autoloading of all classes of this library is made through composer's autoloader.

## Basic Usage

You can get the singleton php-system object by doing the following:

```php

use PhpExtended\System\OperatingSystem;

$system = OperatingSystem::get();

```

This system object have lots of useful properties. An instance of `UnknownOs` is returned in case this is not implemented.
The `get` method never throws any exception.

## License

MIT (See [license file](LICENSE)).
