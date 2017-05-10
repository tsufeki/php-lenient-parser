<?php

namespace PhpLenientParser;

use PhpLenientParser\Expression\ExpressionParserInterface;
use PhpLenientParser\Statement\StatementParserInterface;
use PhpParser\ErrorHandler;
use PhpParser\Lexer;

class LenientParser
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var Lexer
     */
    private $lexer;

    /**
     * @var ExpressionParserInterface
     */
    private $expressionParser;

    /**
     * @var StatementParserInterface
     */
    private $statementParser;

    /**
     * @var StatementParserInterface
     */
    private $topLevelParser;

    /**
     * @param array                     $options
     * @param Lexer                     $lexer
     * @param ExpressionParserInterface $expressionParser
     * @param StatementParserInterface  $statementParser
     * @param StatementParserInterface  $topLevelParser
     */
    public function __construct(
        array $options,
        $lexer,
        ExpressionParserInterface $expressionParser,
        StatementParserInterface $statementParser,
        StatementParserInterface $topLevelParser
    ) {
        $this->options = $options;
        $this->lexer = $lexer;
        $this->expressionParser = $expressionParser;
        $this->statementParser = $statementParser;
        $this->topLevelParser = $topLevelParser;
    }

    public function parse(string $code, ErrorHandler $errorHandler = null)
    {
        if ($errorHandler === null) {
            $errorHandler = new ErrorHandler\Throwing();
        }

        $parserState = $this->createParserState($code, $errorHandler);
        $statements = [];
        while (true) {
            $stmts = $this->topLevelParser->parseList($parserState);
            $statements = array_merge($statements, $stmts);
            if ($parserState->lookAhead()->type !== 0) {
                // drop the errorneous token
                $parserState->unexpected($parserState->eat());
            } else {
                break;
            }
        }

        return $statements;
    }

    /**
     * @param string       $code
     * @param ErrorHandler $errorHandler
     *
     * @return ParserStateInterface
     */
    protected function createParserState(string $code, ErrorHandler $errorHandler): ParserStateInterface
    {
        $this->lexer->startLexing($code, $errorHandler);

        return new ParserState(
            $this->lexer,
            $errorHandler,
            $this->options,
            $this->expressionParser,
            $this->statementParser
        );
    }
}
