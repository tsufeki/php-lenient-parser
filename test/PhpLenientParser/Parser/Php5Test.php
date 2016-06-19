<?php

namespace PhpLenientParser\Parser;

use PhpLenientParser\Lexer;
use PhpLenientParser\ParserTest;

require_once __DIR__ . '/../ParserTest.php';

class Php5Test extends ParserTest {
    protected function getParser(Lexer $lexer) {
        return new Php5($lexer);
    }
}
