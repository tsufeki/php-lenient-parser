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
     * @param int $token
     * @param int $refToken
     * @param int $precedence
     */
    public function __construct(int $token, int $refToken, int $precedence)
    {
        parent::__construct($token, $precedence, Expr\Assign::class);
        $this->refToken = $refToken;
        $this->refNodeClass = Expr\AssignRef::class;
    }

    public function parse(ParserStateInterface $parser, Node $left)
    {
        $token = $parser->eat();
        $isRef = $parser->eat($this->refToken) !== null;
        $right = $parser->getExpressionParser()->parseOrError($parser, $this->getPrecedence() - 1);

        $class = $isRef ? $this->refNodeClass : $this->getNodeClass();
        return $parser->setAttributes(new $class($left, $right), $left, $right);
    }
}
