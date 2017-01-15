<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class ArrayIndex extends AbstractOperator implements InfixInterface
{
    /**
     * @var int
     */
    private $closeToken;

    /**
     * @param int $token
     * @param int $closeToken
     * @param int $precedence
     */
    public function __construct($token, $closeToken, $precedence)
    {
        parent::__construct($token, $precedence, Node\Expr\ArrayDimFetch::class);
        $this->closeToken = $closeToken;
    }

    public function parse(ParserStateInterface $parser, Node $left)
    {
        $token = $parser->eat();
        $right = $parser->getExpressionParser()->parse($parser);
        $parser->assert($this->closeToken);

        $class = $this->getNodeClass();
        return $parser->setAttributes(new $class($left, $right), $left, $parser->last());
    }
}
