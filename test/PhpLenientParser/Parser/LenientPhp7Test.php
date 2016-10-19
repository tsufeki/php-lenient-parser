<?php

namespace PhpLenientParser\Parser;

use PhpParser\Lexer;
use PhpLenientParser\ParserTest;

require_once __DIR__ . '/../ParserTest.php';

class LenientPhp7Test extends ParserTest {
    protected function getParser(Lexer $lexer) {
        return new LenientPhp7($lexer);
    }
}
