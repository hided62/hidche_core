# php-tail
A smart way to tail files depending of the environment

## Installation

The installation of this library is made via composer.
Download `composer.phar` from [their website](https://getcomposer.org/download/).
Then add to your composer.json :

```json
	"require": {
		...
		"php-extended/php-tail": "^2.0",
		...
	}
```

Then run `php composer.phar update` to install this library.
The autoloading of all classes of this library is made through composer's autoloader.

## Usage

The basic usage of this library is as follows :

```php
use PhpExtended\Tail\Tail;
use PhpExtended\Tail\TailException;

$filename = "/../path/to/my/file.ext";
$tail = new Tail($filename);

try
{
	// 10 is the number of lines you want
	// 200 is the average number of chars on each line (optional)
	// false is to force throwing exceptions (optional, use true if you want silent mode)
	$lines = $tail->smart(10, 200, false);
}
catch(TailException $e)
{
	// do something is case it fails
}

```

This library proposes 6 methods to tail a file, which can be more or less 
performant depending on the context. They each follow the same signature 
(see sample code above).

Those methods are :
- `naive` : Loads the whole file into php, then retains only the last lines
- `cheat` : Uses underlying unix `tail -n` function
- `single` : Uses a signle byte buffer to read backwards the file
- `simple` : Uses a fixed size buffer to read backwards the file
- `dynamic` : Uses a dynamically sized buffer to read backwards the file
- `smart` : Tries to choose the best among those five to be the fastest (recommanded)

This library is inspired from this [specific stackoverflow topic](http://stackoverflow.com/questions/15025875/what-is-the-best-way-in-php-to-read-last-lines-from-a-file/15025877).


## License
MIT (See [license file](LICENSE)).
