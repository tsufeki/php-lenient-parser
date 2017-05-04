<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class Infix extends AbstractOperator implements InfixInterface
{
    /**
     * @var int
     */
    private $rightPrecedence;

    /**
     * @param int    $token
     * @param int    $precedence
     * @param string $nodeClass
     * @param bool   $rightAssociative
     * @param int    $rightPrecedence  Precedence when binding to the right, defaults to $precedence.
     */
    public function __construct(
        int $token,
        int $precedence,
        string $nodeClass,
        bool $rightAssociative = false,
        int $rightPrecedence = null
    ) {
        parent::__construct($token, $precedence, $nodeClass);
        $this->rightPrecedence = ($rightPrecedence ?? $precedence) - ($rightAssociative ? 1 : 0);
    }

    public function parse(ParserStateInterface $parser, Node $left)
    {
        $token = $parser->eat();
        $right = $parser->getExpressionParser()->parseOrError($parser, $this->rightPrecedence);

        $class = $this->getNodeClass();

        return $parser->setAttributes(new $class($left, $right), $left, $right);
    }
}
