<?php

namespace PhpLenientParser;

use PhpLenientParser\Expression\Assign;
use PhpLenientParser\Expression\DNumber;
use PhpLenientParser\Expression\ExpressionParser;
use PhpLenientParser\Expression\ExpressionParserInterface;
use PhpLenientParser\Expression\Infix;
use PhpLenientParser\Expression\LNumber;
use PhpLenientParser\Expression\Nullary;
use PhpLenientParser\Expression\Parens;
use PhpLenientParser\Expression\Postfix;
use PhpLenientParser\Expression\Prefix;
use PhpLenientParser\Expression\String_;
use PhpLenientParser\Expression\Ternary;
use PhpLenientParser\Expression\Variable;
use PhpLenientParser\Statement\ExpressionStatement;
use PhpLenientParser\Statement\StatementParser;
use PhpParser\ErrorHandler;
use PhpParser\Lexer;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar;
use PhpParser\Parser as ParserInterface;
use PhpParser\Parser\Tokens;

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

        $expressionParser->addInfix(new Infix(Tokens::T_LOGICAL_OR, 10, Expr\BinaryOp\LogicalOr::class));
        $expressionParser->addInfix(new Infix(Tokens::T_LOGICAL_XOR, 20, Expr\BinaryOp\LogicalXor::class));
        $expressionParser->addInfix(new Infix(Tokens::T_LOGICAL_AND, 30, Expr\BinaryOp\LogicalAnd::class));

        $expressionParser->addInfix(new Assign(ord('='), ord('&'), 40));
        $expressionParser->addInfix(new Infix(Tokens::T_AND_EQUAL, 40, Expr\AssignOp\BitwiseAnd::class, true));
        $expressionParser->addInfix(new Infix(Tokens::T_OR_EQUAL, 40, Expr\AssignOp\BitwiseOr::class, true));
        $expressionParser->addInfix(new Infix(Tokens::T_XOR_EQUAL, 40, Expr\AssignOp\BitwiseXor::class, true));
        $expressionParser->addInfix(new Infix(Tokens::T_CONCAT_EQUAL, 40, Expr\AssignOp\BitwiseXor::class, true));
        $expressionParser->addInfix(new Infix(Tokens::T_DIV_EQUAL, 40, Expr\AssignOp\Div::class, true));
        $expressionParser->addInfix(new Infix(Tokens::T_MINUS_EQUAL, 40, Expr\AssignOp\Minus::class, true));
        $expressionParser->addInfix(new Infix(Tokens::T_MOD_EQUAL, 40, Expr\AssignOp\Mod::class, true));
        $expressionParser->addInfix(new Infix(Tokens::T_MUL_EQUAL, 40, Expr\AssignOp\Mul::class, true));
        $expressionParser->addInfix(new Infix(Tokens::T_PLUS_EQUAL, 40, Expr\AssignOp\Plus::class, true));
        $expressionParser->addInfix(new Infix(Tokens::T_POW_EQUAL, 40, Expr\AssignOp\Pow::class, true));
        $expressionParser->addInfix(new Infix(Tokens::T_SL_EQUAL, 40, Expr\AssignOp\ShiftLeft::class, true));
        $expressionParser->addInfix(new Infix(Tokens::T_SR_EQUAL, 40, Expr\AssignOp\ShiftRight::class, true));

        $expressionParser->addInfix(new Ternary(ord('?'), ord(':'), 50, Expr\Ternary::class));
        $expressionParser->addInfix(new Infix(Tokens::T_COALESCE, 60, Expr\BinaryOp\Coalesce::class, true));

        $expressionParser->addInfix(new Infix(Tokens::T_BOOLEAN_OR, 70, Expr\BinaryOp\BooleanOr::class));
        $expressionParser->addInfix(new Infix(Tokens::T_BOOLEAN_AND, 80, Expr\BinaryOp\BooleanAnd::class));

        $expressionParser->addInfix(new Infix(ord('|'), 90, Expr\BinaryOp\BitwiseOr::class));
        $expressionParser->addInfix(new Infix(ord('^'), 100, Expr\BinaryOp\BitwiseXor::class));
        $expressionParser->addInfix(new Infix(ord('&'), 110, Expr\BinaryOp\BitwiseAnd::class));

        $expressionParser->addInfix(new Infix(Tokens::T_IS_EQUAL, 120, Expr\BinaryOp\Equal::class));
        $expressionParser->addInfix(new Infix(Tokens::T_IS_NOT_EQUAL, 120, Expr\BinaryOp\NotEqual::class));
        $expressionParser->addInfix(new Infix(Tokens::T_IS_IDENTICAL, 120, Expr\BinaryOp\Identical::class));
        $expressionParser->addInfix(new Infix(Tokens::T_IS_NOT_IDENTICAL, 120, Expr\BinaryOp\NotIdentical::class));
        $expressionParser->addInfix(new Infix(Tokens::T_SPACESHIP, 120, Expr\BinaryOp\Spaceship::class));

        $expressionParser->addInfix(new Infix(ord('<'), 130, Expr\BinaryOp\Smaller::class));
        $expressionParser->addInfix(new Infix(ord('>'), 130, Expr\BinaryOp\Greater::class));
        $expressionParser->addInfix(new Infix(Tokens::T_IS_SMALLER_OR_EQUAL, 130, Expr\BinaryOp\SmallerOrEqual::class));
        $expressionParser->addInfix(new Infix(Tokens::T_IS_GREATER_OR_EQUAL, 130, Expr\BinaryOp\GreaterOrEqual::class));

        $expressionParser->addInfix(new Infix(Tokens::T_SL, 140, Expr\BinaryOp\ShiftLeft::class));
        $expressionParser->addInfix(new Infix(Tokens::T_SR, 140, Expr\BinaryOp\ShiftRight::class));

        $expressionParser->addInfix(new Infix(ord('+'), 150, Expr\BinaryOp\Plus::class));
        $expressionParser->addInfix(new Infix(ord('-'), 150, Expr\BinaryOp\Minus::class));
        $expressionParser->addInfix(new Infix(ord('.'), 150, Expr\BinaryOp\Concat::class));

        $expressionParser->addInfix(new Infix(ord('*'), 160, Expr\BinaryOp\Mul::class));
        $expressionParser->addInfix(new Infix(ord('/'), 160, Expr\BinaryOp\Div::class));
        $expressionParser->addInfix(new Infix(ord('%'), 160, Expr\BinaryOp\Mod::class));

        $expressionParser->addPrefix(new Prefix(ord('!'), 170, Expr\BooleanNot::class));

        //TODO: instanceof 180

        $expressionParser->addPrefix(new Prefix(ord('+'), 190, Expr\UnaryPlus::class));
        $expressionParser->addPrefix(new Prefix(ord('-'), 190, Expr\UnaryMinus::class));
        $expressionParser->addPrefix(new Prefix(ord('~'), 190, Expr\BitwiseNot::class));
        $expressionParser->addPrefix(new Prefix(ord('@'), 190, Expr\ErrorSuppress::class));

        $expressionParser->addPrefix(new Prefix(Tokens::T_ARRAY_CAST, 190, Expr\Cast\Array_::class));
        $expressionParser->addPrefix(new Prefix(Tokens::T_BOOL_CAST, 190, Expr\Cast\Bool_::class));
        $expressionParser->addPrefix(new Prefix(Tokens::T_DOUBLE_CAST, 190, Expr\Cast\Double::class));
        $expressionParser->addPrefix(new Prefix(Tokens::T_INT_CAST, 190, Expr\Cast\Int_::class));
        $expressionParser->addPrefix(new Prefix(Tokens::T_OBJECT_CAST, 190, Expr\Cast\Object_::class));
        $expressionParser->addPrefix(new Prefix(Tokens::T_STRING_CAST, 190, Expr\Cast\String_::class));
        $expressionParser->addPrefix(new Prefix(Tokens::T_UNSET_CAST, 190, Expr\Cast\Unset_::class));

        $expressionParser->addInfix(new Infix(Tokens::T_POW, 200, Expr\BinaryOp\Pow::class, true));

        $expressionParser->addPrefix(new Prefix(Tokens::T_INC, 210, Expr\PreInc::class));
        $expressionParser->addPrefix(new Prefix(Tokens::T_DEC, 210, Expr\PreDec::class));
        $expressionParser->addInfix(new Postfix(Tokens::T_INC, 210, Expr\PostInc::class));
        $expressionParser->addInfix(new Postfix(Tokens::T_DEC, 210, Expr\PostInc::class));

        //TODO: [] 220
        //TODO: clone 230
        //TODO: new 230
        //TODO: -> 240 MethodCall PropertyFetch
        //TODO: :: 240 ClassConstFetch StaticCall StaticPropertyFetch

        $expressionParser->addPrefix(new LNumber(Tokens::T_LNUMBER));
        $expressionParser->addPrefix(new DNumber(Tokens::T_DNUMBER));
        $expressionParser->addPrefix(new String_(Tokens::T_CONSTANT_ENCAPSED_STRING));
        //TODO: encapsed
        //TODO: heredoc

        $expressionParser->addPrefix(new Nullary(Tokens::T_CLASS_C, Scalar\MagicConst\Class_::class));
        $expressionParser->addPrefix(new Nullary(Tokens::T_DIR, Scalar\MagicConst\Dir::class));
        $expressionParser->addPrefix(new Nullary(Tokens::T_FILE, Scalar\MagicConst\File::class));
        $expressionParser->addPrefix(new Nullary(Tokens::T_FUNC_C, Scalar\MagicConst\Function_::class));
        $expressionParser->addPrefix(new Nullary(Tokens::T_LINE, Scalar\MagicConst\Line::class));
        $expressionParser->addPrefix(new Nullary(Tokens::T_METHOD_C, Scalar\MagicConst\Method::class));
        $expressionParser->addPrefix(new Nullary(Tokens::T_NS_C, Scalar\MagicConst\Namespace_::class));
        $expressionParser->addPrefix(new Nullary(Tokens::T_TRAIT_C, Scalar\MagicConst\Trait_::class));

        //TODO: [] array() list()
        //TODO: () empty() eval() exit die include require isset() print FuncCall

        $expressionParser->addPrefix(new Parens(ord('('), ord(')')));
        //TODO: ConstFetch Variable
        $expressionParser->addPrefix(new Variable(Tokens::T_VARIABLE));

        //TODO: function
        //TODO: ShellExec
        //TODO: yield yield from

        return $expressionParser;
    }

    protected function createStatementParser()
    {
        $statementParser = new StatementParser();

        $statementParser->addStatement(new ExpressionStatement());

        return $statementParser;
    }
}
