<?php

namespace PhpLenientParser\Parser;

use PhpLenientParser\Lexer;
use PhpLenientParser\ParserTest;

require_once __DIR__ . '/../ParserTest.php';

class Php7Test extends ParserTest {
    protected function getParser(Lexer $lexer) {
        return new Php7($lexer);
    }
}
