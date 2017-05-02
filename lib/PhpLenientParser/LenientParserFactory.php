<?php

namespace PhpLenientParser;

use PhpLenientParser\Expression\ArgumentList;
use PhpLenientParser\Expression\ArrayIndex;
use PhpLenientParser\Expression\Array_;
use PhpLenientParser\Expression\Assign;
use PhpLenientParser\Expression\Closure;
use PhpLenientParser\Expression\DNumber;
use PhpLenientParser\Expression\Encapsed;
use PhpLenientParser\Expression\Exit_;
use PhpLenientParser\Expression\ExpressionParser;
use PhpLenientParser\Expression\ExpressionParserInterface;
use PhpLenientParser\Expression\FunctionCall;
use PhpLenientParser\Expression\HereDoc;
use PhpLenientParser\Expression\Identifier;
use PhpLenientParser\Expression\Include_;
use PhpLenientParser\Expression\IndirectVariable;
use PhpLenientParser\Expression\Infix;
use PhpLenientParser\Expression\InstanceOf_;
use PhpLenientParser\Expression\Isset_;
use PhpLenientParser\Expression\LNumber;
use PhpLenientParser\Expression\Name;
use PhpLenientParser\Expression\NameOrConst;
use PhpLenientParser\Expression\New_;
use PhpLenientParser\Expression\Nullary;
use PhpLenientParser\Expression\ObjectAccess;
use PhpLenientParser\Expression\ObjectAccessNew;
use PhpLenientParser\Expression\Parens;
use PhpLenientParser\Expression\Postfix;
use PhpLenientParser\Expression\Prefix;
use PhpLenientParser\Expression\Scope;
use PhpLenientParser\Expression\ScopeNew;
use PhpLenientParser\Expression\SpecialFunction;
use PhpLenientParser\Expression\String_;
use PhpLenientParser\Expression\Ternary;
use PhpLenientParser\Expression\Variable;
use PhpLenientParser\Expression\Yield_;
use PhpLenientParser\Statement\Block;
use PhpLenientParser\Statement\Declare_;
use PhpLenientParser\Statement\DoWhile;
use PhpLenientParser\Statement\Echo_;
use PhpLenientParser\Statement\ExpressionStatement;
use PhpLenientParser\Statement\For_;
use PhpLenientParser\Statement\Foreach_;
use PhpLenientParser\Statement\Function_;
use PhpLenientParser\Statement\Global_;
use PhpLenientParser\Statement\GoTo_;
use PhpLenientParser\Statement\If_;
use PhpLenientParser\Statement\InlineHtml;
use PhpLenientParser\Statement\Label;
use PhpLenientParser\Statement\Nop;
use PhpLenientParser\Statement\ParameterList;
use PhpLenientParser\Statement\Simple;
use PhpLenientParser\Statement\StatementParser;
use PhpLenientParser\Statement\StatementParserInterface;
use PhpLenientParser\Statement\Static_;
use PhpLenientParser\Statement\Switch_;
use PhpLenientParser\Statement\Try_;
use PhpLenientParser\Statement\Type;
use PhpLenientParser\Statement\Unset_;
use PhpLenientParser\Statement\While_;
use PhpParser\Lexer;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt;
use PhpParser\Parser;
use PhpParser\Parser\Tokens;

class LenientParserFactory
{
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
    public function create(int $kind = self::ONLY_PHP7, $lexer = null, array $parserOptions = []): Parser
    {
        if ($kind !== self::ONLY_PHP7) {
            throw new \LogicException(
                'Kind must be ::ONLY_PHP7'
            );
        }

        if (null === $lexer) {
            $lexer = new Lexer\Emulative();
        }

        $expressionParser = new ExpressionParser();

        $identifier = new Identifier();
        $name = new Name(Tokens::T_STRING);
        $variable = new Variable(Tokens::T_VARIABLE);
        $indirectVariable = new IndirectVariable(ord('$'), $variable);
        $type = new Type($name, $identifier);
        $arguments = new ArgumentList();
        $parameters = new ParameterList($type, $variable);

        $classRef = new ExpressionParser();
        $classRef->addPrefix($variable);
        $classRef->addPrefix($indirectVariable);
        $classRef->addPrefix(new Name(Tokens::T_NS_SEPARATOR));
        $classRef->addPrefix(new Name(Tokens::T_STRING));
        $classRef->addInfix(new ArrayIndex(ord('['), ord(']'), 230));
        $classRef->addInfix(new ArrayIndex(ord('{'), ord('}'), 230));
        $classRef->addInfix(
            new ObjectAccessNew(Tokens::T_OBJECT_OPERATOR, 240,
            $identifier, $variable, $indirectVariable)
        );
        $classRef->addInfix(
            new ScopeNew(Tokens::T_PAAMAYIM_NEKUDOTAYIM, 240,
            $variable, $indirectVariable)
        );

        $expressionParser->addPrefix($variable);
        $expressionParser->addPrefix($indirectVariable);
        $expressionParser->addPrefix(new NameOrConst(Tokens::T_NS_SEPARATOR));
        $expressionParser->addPrefix(new NameOrConst(Tokens::T_STRING));

        $expressionParser->addInfix(new Infix(Tokens::T_LOGICAL_OR, 10, Expr\BinaryOp\LogicalOr::class));
        $expressionParser->addInfix(new Infix(Tokens::T_LOGICAL_XOR, 20, Expr\BinaryOp\LogicalXor::class));
        $expressionParser->addInfix(new Infix(Tokens::T_LOGICAL_AND, 30, Expr\BinaryOp\LogicalAnd::class));

        $expressionParser->addInfix(new Assign(ord('='), ord('&'), 40));
        $expressionParser->addInfix(new Infix(Tokens::T_AND_EQUAL, 40, Expr\AssignOp\BitwiseAnd::class, true));
        $expressionParser->addInfix(new Infix(Tokens::T_OR_EQUAL, 40, Expr\AssignOp\BitwiseOr::class, true));
        $expressionParser->addInfix(new Infix(Tokens::T_XOR_EQUAL, 40, Expr\AssignOp\BitwiseXor::class, true));
        $expressionParser->addInfix(new Infix(Tokens::T_CONCAT_EQUAL, 40, Expr\AssignOp\Concat::class, true));
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

        $expressionParser->addInfix(new InstanceOf_(Tokens::T_INSTANCEOF, 180, $classRef));

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
        $expressionParser->addInfix(new Postfix(Tokens::T_DEC, 210, Expr\PostDec::class));

        $expressionParser->addPrefix(new Prefix(Tokens::T_CLONE, 220, Expr\Clone_::class));
        $expressionParser->addPrefix(new New_(Tokens::T_NEW, $classRef, $arguments));

        $expressionParser->addInfix(new ArrayIndex(ord('['), ord(']'), 230));
        $expressionParser->addInfix(new ArrayIndex(ord('{'), ord('}'), 230));

        $expressionParser->addInfix(new FunctionCall(ord('('), 240, $arguments));
        $expressionParser->addInfix(
            new ObjectAccess(Tokens::T_OBJECT_OPERATOR, 240,
            $identifier, $variable, $indirectVariable, $arguments)
        );
        $expressionParser->addInfix(
            new Scope(Tokens::T_PAAMAYIM_NEKUDOTAYIM, 240,
            $identifier, $variable, $indirectVariable, $arguments)
        );

        $expressionParser->addPrefix(new LNumber(Tokens::T_LNUMBER));
        $expressionParser->addPrefix(new DNumber(Tokens::T_DNUMBER));
        $expressionParser->addPrefix(new String_(Tokens::T_CONSTANT_ENCAPSED_STRING));
        $expressionParser->addPrefix(new Encapsed(ord('"'), Scalar\Encapsed::class, $identifier, $variable));
        $expressionParser->addPrefix(new Encapsed(ord('`'), Expr\ShellExec::class, $identifier, $variable));
        $expressionParser->addPrefix(new HereDoc($identifier, $variable));

        $expressionParser->addPrefix(new Nullary(Tokens::T_CLASS_C, Scalar\MagicConst\Class_::class));
        $expressionParser->addPrefix(new Nullary(Tokens::T_DIR, Scalar\MagicConst\Dir::class));
        $expressionParser->addPrefix(new Nullary(Tokens::T_FILE, Scalar\MagicConst\File::class));
        $expressionParser->addPrefix(new Nullary(Tokens::T_FUNC_C, Scalar\MagicConst\Function_::class));
        $expressionParser->addPrefix(new Nullary(Tokens::T_LINE, Scalar\MagicConst\Line::class));
        $expressionParser->addPrefix(new Nullary(Tokens::T_METHOD_C, Scalar\MagicConst\Method::class));
        $expressionParser->addPrefix(new Nullary(Tokens::T_NS_C, Scalar\MagicConst\Namespace_::class));
        $expressionParser->addPrefix(new Nullary(Tokens::T_TRAIT_C, Scalar\MagicConst\Trait_::class));

        $expressionParser->addPrefix(
            new Array_(ord('['), null, ord(']'), Expr\Array_::class, Expr\Array_::KIND_SHORT)
        );
        $expressionParser->addPrefix(
            new Array_(Tokens::T_ARRAY, ord('('), ord(')'), Expr\Array_::class, Expr\Array_::KIND_LONG)
        );
        $expressionParser->addPrefix(
            new Array_(Tokens::T_LIST, ord('('), ord(')'), Expr\List_::class)
        );

        $expressionParser->addPrefix(new Yield_(30));
        $expressionParser->addPrefix(new SpecialFunction(Tokens::T_YIELD_FROM, Expr\YieldFrom::class, false, 30));
        $expressionParser->addPrefix(new SpecialFunction(Tokens::T_PRINT, Expr\Print_::class));
        $expressionParser->addPrefix(new SpecialFunction(Tokens::T_EMPTY, Expr\Empty_::class, true));
        $expressionParser->addPrefix(new SpecialFunction(Tokens::T_EVAL, Expr\Eval_::class, true));

        $expressionParser->addPrefix(new Include_(Tokens::T_INCLUDE));
        $expressionParser->addPrefix(new Include_(Tokens::T_INCLUDE_ONCE));
        $expressionParser->addPrefix(new Include_(Tokens::T_REQUIRE));
        $expressionParser->addPrefix(new Include_(Tokens::T_REQUIRE_ONCE));

        $expressionParser->addPrefix(new Exit_());
        $expressionParser->addPrefix(new Isset_());

        $expressionParser->addPrefix(new Closure(Tokens::T_FUNCTION, $type, $parameters, $variable));
        $expressionParser->addPrefix(new Closure(Tokens::T_STATIC, $type, $parameters, $variable));

        $expressionParser->addPrefix(new Parens(ord('('), ord(')')));

        //TODO: precedence of prefix operators with assignment: !$a = 7

        $statementParser = new StatementParser();

        $statementParser->addStatement(new ExpressionStatement());
        $statementParser->addStatement(new Echo_());
        $statementParser->addStatement(new InlineHtml());
        $statementParser->addStatement(new Unset_());
        $statementParser->addStatement(new Nop());
        $statementParser->addStatement(new Static_($variable));
        $statementParser->addStatement(new Global_($variable, $indirectVariable));
        $statementParser->addStatement(new Label($identifier));
        $statementParser->addStatement(new GoTo_($identifier));

        $statementParser->addStatement(new Block());
        $statementParser->addStatement(new If_());
        $statementParser->addStatement(new While_());
        $statementParser->addStatement(new DoWhile());
        $statementParser->addStatement(new For_());
        $statementParser->addStatement(new Foreach_());
        $statementParser->addStatement(new Switch_());
        $statementParser->addStatement(new Try_($variable, $name));
        $statementParser->addStatement(new Declare_($identifier));

        $statementParser->addStatement(new Simple(Tokens::T_BREAK, Stmt\Break_::class));
        $statementParser->addStatement(new Simple(Tokens::T_CONTINUE, Stmt\Continue_::class));
        $statementParser->addStatement(new Simple(Tokens::T_RETURN, Stmt\Return_::class));
        $statementParser->addStatement(new Simple(Tokens::T_THROW, Stmt\Throw_::class, true));

        $statementParser->addStatement(new Function_($parameters, $type, $identifier));

        return new LenientParser(
            $parserOptions,
            $lexer,
            $expressionParser,
            $statementParser
        );
    }
}
