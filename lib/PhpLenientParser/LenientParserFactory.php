<?php

namespace PhpLenientParser;

use PhpParser\Lexer;
use PhpParser\Parser;

class LenientParserFactory {
    const ONLY_PHP7 = 3;

    /**
     * Creates a Parser instance, according to the provided kind.
     *
     * @param int        $kind  ::ONLY_PHP7 is the only option.
     * @param Lexer|null $lexer Lexer to use.
     * @param array      $parserOptions Parser options. See ParserAbstract::__construct() argument
     *
     * @return Parser The parser instance
     */
    public function create($kind = self::ONLY_PHP7, $lexer = null, array $parserOptions = array()) {
        if (null === $lexer) {
            $lexer = new Lexer\Emulative();
        }
        switch ($kind) {
            case self::ONLY_PHP7:
                return new LenientParser($lexer, $parserOptions);
            default:
                throw new \LogicException(
                    'Kind must be ::ONLY_PHP7'
                );
        }
    }
}
