<?php

namespace PhpLenientParser;

use PhpParser\Lexer as BaseLexer;

class ParserFactory {
    const ONLY_PHP7 = 3;
    const ONLY_PHP5 = 4;

    /**
     * Creates a Parser instance, according to the provided kind.
     *
     * @param int            $kind  One of ::ONLY_PHP7 or ::ONLY_PHP5
     * @param BaseLexer|null $lexer Lexer to use.
     * @param array          $parserOptions Parser options. See ParserAbstract::__construct() argument
     *
     * @return Parser The parser instance
     */
    public function create($kind, BaseLexer $lexer = null, array $parserOptions = array()) {
        if (null === $lexer) {
            $lexer = new Lexer\Lenient();
        }
        switch ($kind) {
            case self::ONLY_PHP7:
                return new Parser\LenientPhp7($lexer, $parserOptions);
            case self::ONLY_PHP5:
                return new Parser\LenientPhp5($lexer, $parserOptions);
            default:
                throw new \LogicException(
                    'Kind must be one of ::ONLY_PHP7 or ::ONLY_PHP5'
                );
        }
    }
}
