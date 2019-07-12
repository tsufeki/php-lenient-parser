<?php declare(strict_types=1);

namespace PhpLenientParser;

use PhpLenientParser\Expression\ExpressionParserInterface;
use PhpLenientParser\Statement\StatementParserInterface;
use PhpParser\ErrorHandler;
use PhpParser\Lexer;
use PhpParser\Parser;

class LenientParser implements Parser
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

    public function __construct(
        array $options,
        Lexer $lexer,
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

    public function parse(string $code, ?ErrorHandler $errorHandler = null): ?array
    {
        if ($errorHandler === null) {
            $errorHandler = new ErrorHandler\Throwing();
        }

        $parserState = $this->createParserState($code, $errorHandler);
        $statements = [];
        while (true) {
            $stmts = $this->topLevelParser->parseList($parserState);
            $statements = array_merge($statements, $stmts);
            if (!$parserState->isNext(0)) {
                // drop the errorneous token
                $parserState->unexpected($parserState->eat());
            } else {
                break;
            }
        }

        return $statements;
    }

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
