PHP Lenient Parser
==================

This is a PHP 7 parser written in PHP. It handles some partial and
illegal code, producing best-effort partial syntax tree. Its purpose is to
simplify static code analysis and manipulation.

It's error reporting is not complete though, i.e. it may silently accept illegal
PHP input.

As this is just a parser replacement for [PHP Parser](https://github.com/nikic/PHP-Parser),
go there for actual documentation.


Installation
------------

The preferred installation method is [composer](https://getcomposer.org):

    php composer.phar require tsufeki/php-lenient-parser


Usage
-----

Use the factory to get an instance of parser:

```php
$parser = (new LenientParserFactory())->create();
```

Or provide your own lexer and options (no options are supported at the moment though):

```php
$parser = (new LenientParserFactory())->create(LenientParserFactory::ONLY_PHP7, $lexer, $options);
```

Created parser is a drop-in replacement for `php-parser`.

Do not instantiate `LenientParser` directly, use the factory.


Info
----

Lenient parser requires `nikic/php-parser` 4.0 (i.e. currently `master` branch)
for node classes definitions and such.

At the moment parsing of PHP 5 is not supported, only PHP 7.

Internally, the parser is implemented as a Pratt parser (also called "Top down
operator precedence parser").


License
-------

Same as `php-parser`, BSD - see [LICENCE](LICENSE).

Test suite and CLI script (below `bin/`, `test/` and `test_old/`) are borrowed
from `php-parser` and modified.

