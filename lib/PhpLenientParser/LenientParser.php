<?php

namespace PhpLenientParser;

use PhpLenientParser\Expression\ExpressionParser;
use PhpLenientParser\Expression\ExpressionParserInterface;
use PhpLenientParser\Expression\Infix;
use PhpLenientParser\Expression\Parens;
use PhpLenientParser\Expression\Postfix;
use PhpLenientParser\Expression\Prefix;
use PhpLenientParser\Expression\Variable;
use PhpParser\ErrorHandler;
use PhpParser\Lexer;
use PhpParser\Node\Expr;
use PhpParser\Parser as ParserInterface;
use PhpParser\Parser\Tokens;
use PhpLenientParser\Statement\StatementParser;
use PhpLenientParser\Statement\ExpressionStatement;

class LenientParser implements ParserInterface
{
    /**
     * @var Lexer
     */
    private $lexer;

    /**
     * @var array
     */
    private $options;

    /**
     * @param Lexer $lexer
     * @param array $options
     */
    public function __construct($lexer, array $options = [])
    {
        $this->lexer = $lexer;
        $this->options = $options;
    }

    public function parse($code, ErrorHandler $errorHandler = null)
    {
        if ($errorHandler === null) {
            $errorHandler = new ErrorHandler\Throwing();
        }

        $parserState = $this->createParserState($code, $errorHandler);
        $statementParser = $parserState->getStatementParser();
        $statements = [];
        while ($parserState->lookAhead()->type !== 0) {
            $statement = $statementParser->parse($parserState);
            if ($statement !== null) {
                $statements[] = $statement;
            } else {
                // drop the errorneous token
                $parserState->eat(); //TODO add error
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
    protected function createParserState($code, ErrorHandler $errorHandler)
    {
        $this->lexer->startLexing($code, $errorHandler);

        return new ParserState(
            $this->lexer,
            $errorHandler,
            $this->options,
            $this->createExpressionParser(),
            $this->createStatementParser()
        );
    }

    /**
     * @return ExpressionParserInterface
     */
    protected function createExpressionParser()
    {
        $expressionParser = new ExpressionParser();

        $expressionParser->addPrefix(new Parens(ord('('), ord(')')));
        $expressionParser->addPrefix(new Variable());

        $expressionParser->addInfix(new Infix(ord('+'), 150, Expr\BinaryOp\Plus::class));
        $expressionParser->addInfix(new Infix(ord('-'), 150, Expr\BinaryOp\Minus::class));

        $expressionParser->addInfix(new Infix(ord('*'), 160, Expr\BinaryOp\Mul::class));
        $expressionParser->addInfix(new Infix(ord('/'), 160, Expr\BinaryOp\Div::class));
        $expressionParser->addInfix(new Infix(ord('%'), 160, Expr\BinaryOp\Mod::class));

        $expressionParser->addPrefix(new Prefix(ord('+'), 190, Expr\UnaryPlus::class));
        $expressionParser->addPrefix(new Prefix(ord('-'), 190, Expr\UnaryMinus::class));

        $expressionParser->addInfix(new Infix(Tokens::T_POW, 200, Expr\BinaryOp\Pow::class, true));

        $expressionParser->addPrefix(new Prefix(Tokens::T_INC, 210, Expr\PreInc::class));
        $expressionParser->addPrefix(new Prefix(Tokens::T_DEC, 210, Expr\PreDec::class));
        $expressionParser->addInfix(new Postfix(Tokens::T_INC, 210, Expr\PostInc::class));
        $expressionParser->addInfix(new Postfix(Tokens::T_DEC, 210, Expr\PostInc::class));

        return $expressionParser;
    }

    protected function createStatementParser()
    {
        $statementParser = new StatementParser();

        $statementParser->addStatement(new ExpressionStatement());

        return $statementParser;
    }
}
