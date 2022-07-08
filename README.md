# PHP-LAFF Analyzer
PHP Implementation of the Largest Area Fit First (LAFF) 3D (three dimensions: length, width, height) box packing algorithm.

With this library you can easily:
- get the required dimensions of the container that will fit all given packages,
- split packages per defined amount of containers,
- split packages per layer in a given container,
- get information about the wasted amount of space per container and per layer,
- get the number of remaining packages that couldn't fit into given containers,

## Algorithm definition

Implementation of the used algorithm was defined by M. Zahid Gürbüz, Selim Akyokus, Ibrahim Emiroglu, and Aysun Güran in a paper called ["An Efficient Algorithm for 3D Rectangular Box Packing"](http://www.zahidgurbuz.com/yayinlar/An%20Efficient%20Algorithm%20for%203D%20Rectangular%20Box%20Packing.pdf).

## Installation

> **Note**
> To use this library you need PHP in version 8.1+

```bash
composer require php-laff/analyzer
```

## Usage

### Get the size of the required container for selected packages
```php
<?php

use LAFF\Analyzer\Analyzer;
use LAFF\Analyzer\Model\Package;

$packages = [
    new Package(50, 50, 8),
    new Package(33, 8, 8),
    new Package(16, 20, 8),
    new Package(3, 18, 8),
    new Package(14, 12, 8),
];

$analyzer = new Analyzer();
$analyzer->analyze($packages);

$containers = $analyzer->getContainers();
/** @var Container $container */
$container = reset($containers);

var_dump($container->toArray());
// Output:
// array(3) {
//   ["length"]=>
//   int(50)
//   ["width"]=>
//   int(50)
//   ["height"]=>
//   int(16)
// }
var_dump($container->countLayers());
// Output:
// int(2)
var_dump($analyzer->getWastePercentage());
// Output (%):
// int(32)
var_dump($analyzer->getWasteVolume());
// Output (cm3):
// int(13552)
```

### Check how many packages can be fitter into a given container
```php
<?php

use LAFF\Analyzer\Analyzer;
use LAFF\Analyzer\Model\Container;
use LAFF\Analyzer\Model\Package;

$packages = [
    new Package(50, 50, 8),
    new Package(33, 8, 8),
    new Package(16, 20, 8),
    new Package(3, 18, 8),
    new Package(14, 12, 8),
];

$container = new Container(65, 60, 8);

$analyzer = new Analyzer();
$analyzer->analyze($packages, [$container]);

var_dump($container->full);
// Output:
// bool(true)
var_dump($container->countLayers());
// Output:
// int(1)
var_dump($container->getWastePercentage());
// Output (%)
//: int(15)
var_dump($container->getWasteVolume());
// Output (cm3):
// int(4752)
```

## Development
To install dependencies, launch the following commands:
```bash
composer install
```

## Run Tests
To execute full test suite, static analyse or coding style fixed, launch the following commands:
```bash
composer test
composer phpstan
composer cs-fixer
```

## Kudos
There is already a library for LAFF in PHP: [Cloudstek/php-laff](https://github.com/Cloudstek/php-laff); while both use the same algorithm, the internals is different. The main difference between those libraries is that this one can work on an array of containers. I want to say "thank you" for the work on that library in the past!
