<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class Encapsed extends AbstractPrefix
{
    /**
     * @var string
     */
    private $nodeClass;

    /**
     * @var Variable
     */
    private $variableParser;

    /**
     * @var Identifier
     */
    private $identifierParser;

    public function __construct(int $token, string $nodeClass, Identifier $identifierParser, Variable $variableParser)
    {
        parent::__construct($token);
        $this->nodeClass = $nodeClass;
        $this->identifierParser = $identifierParser;
        $this->variableParser = $variableParser;
    }

    public function parse(ParserStateInterface $parser): ?Node\Expr
    {
        $first = $parser->eat();

        $parts = [];
        while ($parser->eatIf($this->getEndToken()) === null && !$parser->isNext(0)) {
            switch ($parser->lookAhead()->type) {
                case Tokens::T_ENCAPSED_AND_WHITESPACE:
                    $parts[] = $this->parseStringPart($parser);
                    break;
                case $this->variableParser->getToken():
                    $parts[] = $this->parseVariable($parser);
                    break;
                case Tokens::T_CURLY_OPEN:
                    $parts[] = $this->parseCurly($parser);
                    break;
                case Tokens::T_DOLLAR_OPEN_CURLY_BRACES:
                    $parts[] = $this->parseDollarCurly($parser);
                    break;
                default:
                    $parser->eat();
            }
        }

        $class = $this->nodeClass;
        /** @var Node\Expr */
        $node = new $class($parts, ['kind' => Node\Scalar\String_::KIND_DOUBLE_QUOTED]);
        $parser->setAttributes($node, $first, $parser->last());

        return $node;
    }

    protected function getEndToken(): int
    {
        return $this->getToken();
    }

    protected function parseStringPart(ParserStateInterface $parser): Node\Scalar\EncapsedStringPart
    {
        $token = $parser->eat();
        $value = $token->value;
        $value = String_::replaceEscapes($value);
        $value = String_::replaceQuoteEscapes($value, chr($this->getToken()));
        $node = new Node\Scalar\EncapsedStringPart($value);
        $parser->setAttributes($node, $token, $token);

        return $node;
    }

    protected function parseVariable(ParserStateInterface $parser): Node\Expr
    {
        $var = $this->variableParser->parse($parser);
        assert($var !== null);

        switch ($parser->lookAhead()->type) {
            case Tokens::T_OBJECT_OPERATOR:
                $parser->eat();
                $id = $this->identifierParser->parse($parser);
                if ($id === null) {
                    $id = $parser->getExpressionParser()->makeErrorNode($parser->last());
                }
                $node = new Node\Expr\PropertyFetch($var, $id);
                $parser->setAttributes($node, $var, $parser->last());

                return $node;

            case ord('['):
                $parser->eat();
                $offset = $this->parseOffset($parser);
                $parser->assert(ord(']'));
                $node = new Node\Expr\ArrayDimFetch($var, $offset);
                $parser->setAttributes($node, $var, $parser->last());

                return $node;

            default:
                return $var;
        }
    }

    protected function parseOffset(ParserStateInterface $parser): ?Node\Expr
    {
        switch ($parser->lookAhead()->type) {
            case Tokens::T_STRING:
                $token = $parser->eat();
                $node = new Node\Scalar\String_($token->value);
                $parser->setAttributes($node, $token, $token);

                return $node;

            case $this->variableParser->getToken():
                return $this->variableParser->parse($parser);

            case ord('-'):
                if ($parser->lookAhead(1)->type === Tokens::T_NUM_STRING) {
                    $token = $parser->eat();
                    $last = $parser->eat();
                    $node = $this->parseNumString('-' . $last->value);
                    $parser->setAttributes($node, $token, $last);

                    return $node;
                }

                return null;

            case Tokens::T_NUM_STRING:
                $token = $parser->eat();
                $node = $this->parseNumString($token->value);
                $parser->setAttributes($node, $token, $token);

                return $node;

            default:
                return null;
        }
    }

    /**
     * @return Node\Scalar\LNumber|Node\Scalar\String_
     */
    protected function parseNumString(string $numString): Node\Scalar
    {
        if (!preg_match('/^(?:0|-?[1-9][0-9]*)$/', $numString)) {
            return new Node\Scalar\String_($numString);
        }

        $number = +$numString;
        if (!is_int($number)) {
            return new Node\Scalar\String_($numString);
        }

        return new Node\Scalar\LNumber($number);
    }

    protected function parseCurly(ParserStateInterface $parser): Node\Expr
    {
        $token = $parser->eat();
        $expr = $parser->getExpressionParser()->parseOrError($parser);
        $parser->assert(ord('}'));

        return $expr;
    }

    protected function parseDollarCurly(ParserStateInterface $parser): Node\Expr
    {
        $token = $parser->eat();
        $expr = null;

        if ($parser->isNext(Tokens::T_STRING_VARNAME)) {
            $expr = new Node\Expr\Variable($parser->eat()->value);
            $parser->setAttributes($expr, $parser->last(), $parser->last());

            if ($parser->isNext(ord('['))) {
                $parser->eat();
                $index = $parser->getExpressionParser()->parse($parser);
                $parser->assert(ord(']'));
                $expr = new Node\Expr\ArrayDimFetch($expr, $index);
            }
        } else {
            $innerExpr = $parser->getExpressionParser()->parseOrError($parser);
            $expr = new Node\Expr\Variable($innerExpr);
        }

        $parser->assert(ord('}'));
        $parser->setAttributes($expr, $token, $parser->last());

        return $expr;
    }
}
