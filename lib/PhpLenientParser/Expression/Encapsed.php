<?php

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

    /**
     * @param int        $token
     * @param string     $nodeClass
     * @param Identifier $identifierParser
     * @param Variable   $variableParser
     */
    public function __construct(int $token, string $nodeClass, Identifier $identifierParser, Variable $variableParser)
    {
        parent::__construct($token);
        $this->nodeClass = $nodeClass;
        $this->identifierParser = $identifierParser;
        $this->variableParser = $variableParser;
    }

    public function parse(ParserStateInterface $parser)
    {
        $first = $parser->eat();

        $parts = [];
        while ($parser->eat($this->getEndToken()) === null && $parser->lookAhead()->type !== 0) {
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

        return $parser->setAttributes(
            new $class($parts, ['kind' => Node\Scalar\String_::KIND_DOUBLE_QUOTED]),
            $first, $parser->last()
        );
    }

    /**
     * @return int
     */
    protected function getEndToken(): int
    {
        return $this->getToken();
    }

    /**
     * @param ParserStateInterface $parser
     *
     * @return Node\Scalar\EncapsedStringPart
     */
    protected function parseStringPart(ParserStateInterface $parser): Node\Scalar\EncapsedStringPart
    {
        $token = $parser->eat();
        $value = $token->value;
        $value = String_::replaceEscapes($value);
        $value = String_::replaceQuoteEscapes($value, chr($this->getToken()));

        return $parser->setAttributes(new Node\Scalar\EncapsedStringPart($value), $token, $token);
    }

    /**
     * @param ParserStateInterface $parser
     *
     * @return Node\Expr
     */
    protected function parseVariable(ParserStateInterface $parser): Node\Expr
    {
        $var = $this->variableParser->parse($parser);

        switch ($parser->lookAhead()->type) {
            case Tokens::T_OBJECT_OPERATOR:
                $parser->eat();
                $id = $this->identifierParser->parse($parser);
                if ($id === null) {
                    $id = $parser->getExpressionParser()->makeErrorNode($parser->last());
                }

                return $parser->setAttributes(new Node\Expr\PropertyFetch($var, $id), $var, $parser->last());
            case ord('['):
                $parser->eat();
                $offset = $this->parseOffset($parser);
                $parser->assert(ord(']'));

                return $parser->setAttributes(new Node\Expr\ArrayDimFetch($var, $offset), $var, $parser->last());
            default:
                return $var;
        }
    }

    /**
     * @param ParserStateInterface $parser
     *
     * @return Node\Expr|null
     */
    protected function parseOffset(ParserStateInterface $parser)
    {
        switch ($parser->lookAhead()->type) {
            case Tokens::T_STRING:
                $token = $parser->eat();

                return $parser->setAttributes(new Node\Scalar\String_($token->value), $token, $token);
            case $this->variableParser->getToken():
                return $this->variableParser->parse($parser);
            case ord('-'):
                if ($parser->lookAhead(1)->type === Tokens::T_NUM_STRING) {
                    $token = $parser->eat();
                    $last = $parser->eat();

                    return $parser->setAttributes($this->parseNumString('-' . $last->value), $token, $last);
                }

                return null;
            case Tokens::T_NUM_STRING:
                $token = $parser->eat();

                return $parser->setAttributes($this->parseNumString($token->value), $token, $token);
            default:
                return null;
        }
    }

    /**
     * @param string $numString
     *
     * @return Node\Scalar\LNumber|Node\Scalar\String_
     */
    protected function parseNumString($numString)
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

    /**
     * @param ParserStateInterface $parser
     *
     * @return Node\Expr
     */
    protected function parseCurly(ParserStateInterface $parser): Node\Expr
    {
        $token = $parser->eat();
        $expr = $parser->getExpressionParser()->parseOrError($parser);
        $parser->assert(ord('}'));

        return $expr;
    }

    /**
     * @param ParserStateInterface $parser
     *
     * @return Node\Expr
     */
    protected function parseDollarCurly(ParserStateInterface $parser): Node\Expr
    {
        $token = $parser->eat();
        $expr = null;

        if ($parser->lookAhead()->type === Tokens::T_STRING_VARNAME) {
            $expr = $parser->setAttributes(new Node\Expr\Variable($parser->eat()->value), $parser->last(), $parser->last());

            if ($parser->lookAhead()->type === ord('[')) {
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

        return $parser->setAttributes($expr, $token, $parser->last());
    }
}
