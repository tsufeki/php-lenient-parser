<?php

namespace PhpLenientParser\Parser;

use PhpParser\Lexer;
use PhpLenientParser\ParserTest;

require_once __DIR__ . '/../ParserTest.php';

class LenientPhp5Test extends ParserTest {
    protected function getParser(Lexer $lexer) {
        return new LenientPhp5($lexer);
    }
}
