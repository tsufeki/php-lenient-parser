<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class Infix extends AbstractOperator implements InfixInterface
{
    /**
     * @var bool
     */
    private $rightAssociative;

    /**
     * @param int $token
     * @param int $precedence
     * @param string $nodeClass
     * @param bool $rightAssociative
     */
    public function __construct($token, $precedence, $nodeClass, $rightAssociative = false)
    {
        parent::__construct($token, $precedence, $nodeClass);
        $this->rightAssociative = $rightAssociative;
    }

    public function parse(ParserStateInterface $parser, Node $left)
    {
        $token = $parser->eat();
        $right = $parser->getExpressionParser()->parseOrError(
            $parser,
            $this->getPrecedence() - ($this->rightAssociative ? 1 : 0)
        );

        $class = $this->getNodeClass();
        return $parser->setAttributes(new $class($left, $right), $left, $right);
    }
}
