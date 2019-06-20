# Convert an array to xml

[![Latest Version](https://img.shields.io/github/release/spatie/array-to-xml.svg?style=flat-square)](https://github.com/spatie/array-to-xml/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/spatie/array-to-xml/master.svg?style=flat-square)](https://travis-ci.org/spatie/array-to-xml)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/array-to-xml.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/array-to-xml)
[![StyleCI](https://styleci.io/repos/32388747/shield?branch=master)](https://styleci.io/repos/32388747)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/array-to-xml.svg?style=flat-square)](https://packagist.org/packages/spatie/array-to-xml)

This package provides a very simple class to convert an array to an xml string.

## Install

You can install this package via composer.

``` bash
composer require spatie/array-to-xml
```

## Usage

```php
use Spatie\ArrayToXml\ArrayToXml;
...
$array = [
    'Good guy' => [
        'name' => 'Luke Skywalker',
        'weapon' => 'Lightsaber'
    ],
    'Bad guy' => [
        'name' => 'Sauron',
        'weapon' => 'Evil Eye'
    ]
];

$result = ArrayToXml::convert($array);
```
After running this piece of code `$result` will contain:

```xml
<?xml version="1.0"?>
<root>
    <Good_guy>
        <name>Luke Skywalker</name>
        <weapon>Lightsaber</weapon>
    </Good_guy>
    <Bad_guy>
        <name>Sauron</name>
        <weapon>Evil Eye</weapon>
    </Bad_guy>
</root>
```

### Setting the name of the root element

Optionally you can set the name of the rootElement by passing it as the second argument. If you don't specify
this argument (or set it to an empty string) "root" will be used.
```
$result = ArrayToXml::convert($array, 'customrootname');
```

### Handling key names

By default all spaces in the key names of your array will be converted to underscores. If you want to opt out of
this behaviour you can set the third argument to false. We'll leave all keynames alone.
```
$result = ArrayToXml::convert($array, 'customrootname', false);
```

### Adding attributes

You can use a key named `_attributes` to add attributes to a node, and `_value` to specify the value.

```php
$array = [
    'Good guy' => [
        '_attributes' => ['attr1' => 'value'],
        'name' => 'Luke Skywalker',
        'weapon' => 'Lightsaber'
    ],
    'Bad guy' => [
        'name' => 'Sauron',
        'weapon' => 'Evil Eye'
    ]
    'The survivor' => [
        '_attributes' => ['house'=>'Hogwarts'],
        '_value' => 'Harry Potter'
    ]
];

$result = ArrayToXml::convert($array);
```

This code will result in:

```xml
<?xml version="1.0"?>
<root>
    <Good_guy attr1="value">
        <name>Luke Skywalker</name>
        <weapon>Lightsaber</weapon>
    </Good_guy>
    <Bad_guy>
        <name>Sauron</name>
        <weapon>Evil Eye</weapon>
    </Bad_guy>
    <The_survivor house="Hogwarts">
        Harry Potter
    </The_survivor>
</root>
```

### Using reserved characters

It is also possible to wrap the value of a node into a CDATA section. This allows you to use reserved characters.

```php
$array = [
    'Good guy' => [
        'name' => [
            '_cdata' => '<h1>Luke Skywalker</h1>'
        ],
        'weapon' => 'Lightsaber'
    ],
    'Bad guy' => [
        'name' => '<h1>Sauron</h1>',
        'weapon' => 'Evil Eye'
    ]
];

$result = ArrayToXml::convert($array);
```

This code will result in:

```xml
<?xml version="1.0"?>
<root>
    <Good_guy>
        <name><![CDATA[<h1>Luke Skywalker</h1>]]></name>
        <weapon>Lightsaber</weapon>
    </Good_guy>
    <Bad_guy>
        <name>&lt;h1&gt;Sauron&lt;/h1&gt;</name>
        <weapon>Evil Eye</weapon>
    </Bad_guy>
</root>
```

If your input contains something that cannot be parsed a `DOMException` will be thrown.

### Adding attributes to the root element

To add attributes to the root element provide an array with an `_attributes` key as the second argument. 
The root element name can then be set using the `rootElementName` key.

```php
$result = ArrayToXml::convert($array, [
    'rootElementName' => 'helloyouluckypeople',
    '_attributes' => [
        'xmlns' => 'https://github.com/spatie/array-to-xml',
    ],
], true, 'UTF-8');
```

### Using a multi-dimensional array

Use a multi-dimensional array to create a collection of elements.
```php
$array = [
    'Good guys' => [
        'Guy' => [
            ['name' => 'Luke Skywalker', 'weapon' => 'Lightsaber'],
            ['name' => 'Captain America', 'weapon' => 'Shield'],
        ],
    ],
    'Bad guys' => [
        'Guy' => [
            ['name' => 'Sauron', 'weapon' => 'Evil Eye'],
            ['name' => 'Darth Vader', 'weapon' => 'Lightsaber'],
        ],
    ],
];
```

This will result in:
```xml
<?xml version="1.0" encoding="UTF-8"?>
<helloyouluckypeople xmlns="https://github.com/spatie/array-to-xml">
    <Good_guys>
        <Guy>
            <name>Luke Skywalker</name>
            <weapon>Lightsaber</weapon>
        </Guy>
        <Guy>
            <name>Captain America</name>
            <weapon>Shield</weapon>
        </Guy>
    </Good_guys>
    <Bad_guys>
        <Guy>
            <name>Sauron</name>
            <weapon>Evil Eye</weapon>
        </Guy>
        <Guy>
            <name>Darth Vader</name>
            <weapon>Lightsaber</weapon>
        </Guy>
    </Bad_guys>
</helloyouluckypeople>
```

### Handling numeric keys

The package can also can handle numeric keys:

```php
$array = [
    100 => [
        'name' => 'Vladimir',
        'nickname' => 'greeflas',
    ],
    200 => [
        'name' => 'Marina',
        'nickname' => 'estacet',
    ],
];

$result = ArrayToXml::convert(['__numeric' => $array]);
```

This will result in:
```xml
<?xml version="1.0" encoding="UTF-8"?>
<root>
    <numeric_100>
        <name>Vladimir</name>
        <nickname>greeflas</nickname>
    </numeric_100>
    <numeric_200>
        <name>Marina</name>
        <nickname>estacet</nickname>
    </numeric_200>
</root>
```

You can change key prefix with setter method called `setNumericTagNamePrefix()`.

## Testing

```bash
vendor/bin/phpunit
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email freek@spatie.be instead of using the issue tracker.

## Postcardware

You're free to use this package, but if it makes it to your production environment we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: Spatie, Samberstraat 69D, 2060 Antwerp, Belgium.

We publish all received postcards [on our company website](https://spatie.be/en/opensource/postcards).

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

## Support us

Spatie is a webdesign agency based in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

Does your business depend on our contributions? Reach out and support us on [Patreon](https://www.patreon.com/spatie). 
All pledges will be dedicated to allocating workforce on maintenance and new awesome stuff.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
