<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Node\Expr;

class Assign extends AbstractOperator implements InfixInterface
{
    /**
     * @var int
     */
    private $refToken;

    /**
     * @var string
     */
    private $refNodeClass;

    /**
     * @var int
     */
    private $rightPrecedence;

    /**
     * @param int $token
     * @param int $refToken
     * @param int $precedence
     * @param int $rightPrecedence Precedence when binding to the right, defaults to $precedence.
     */
    public function __construct(int $token, int $refToken, int $precedence, int $rightPrecedence = null)
    {
        parent::__construct($token, $precedence, Expr\Assign::class);
        $this->refToken = $refToken;
        $this->refNodeClass = Expr\AssignRef::class;
        $this->rightPrecedence = ($rightPrecedence ?? $precedence) - 1;
    }

    public function parse(ParserStateInterface $parser, Node $left)
    {
        $token = $parser->eat();
        $isRef = $parser->eat($this->refToken) !== null;
        $right = $parser->getExpressionParser()->parseOrError($parser, $this->rightPrecedence);

        $class = $isRef ? $this->refNodeClass : $this->getNodeClass();

        return $parser->setAttributes(new $class($left, $right), $left, $right);
    }
}
