<?php

namespace PhpLenientParser;

use PhpLenientParser\Expression\ExpressionParserInterface;
use PhpLenientParser\Statement\StatementParserInterface;
use PhpParser\ErrorHandler;
use PhpParser\Lexer;
use PhpParser\Parser as ParserInterface;
use PhpParser\Parser\Tokens;

class LenientParser implements ParserInterface
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
     * @param array $options
     * @param Lexer $lexer
     * @param ExpressionParserInterface $expressionParser
     * @param StatementParserInterface $statementParser
     */
    public function __construct(
        array $options,
        $lexer,
        ExpressionParserInterface $expressionParser,
        StatementParserInterface $statementParser
    ) {
        $this->options = $options;
        $this->lexer = $lexer;
        $this->expressionParser = $expressionParser;
        $this->statementParser = $statementParser;
    }

    public function parse(string $code, ErrorHandler $errorHandler = null)
    {
        if ($errorHandler === null) {
            $errorHandler = new ErrorHandler\Throwing();
        }

        $parserState = $this->createParserState($code, $errorHandler);
        $statementParser = $parserState->getStatementParser();
        $statements = [];
        while ($parserState->lookAhead()->type !== 0) {
            $stmts = $statementParser->parseList($parserState);
            if (!empty($stmts)) {
                $statements = array_merge($statements, $stmts);
            } else {
                // drop the errorneous token
                $parserState->unexpected($parserState->eat());
            }
        }

        return $statements;
    }

    /**
     * @param string $code
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
