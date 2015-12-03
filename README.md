sccs-phpunit-printer
======================

A PhpUnit result printer (based on kujira-phpunit-printer)

## Requirements

 * PHP 5.5.0 or later.

## Installation

composer global require "sccs/phpunit-printer"

## Configuration

* Add to your phpunit.xml

```xml
    <phpunit
        bootstrap="bootstrap.php"
        colors="true"
        printerFile="vendor/sccs/phpunit-printer/src/Printer.php"
        printerClass="Sccs\PHPUnit\Printer"
    >
```

* Configure your php.ini default_charset to UTF-8
* Configure your terminal to display UTF-8 charset and use a UTF-8 compatible font like DejaVu Sans Mono
