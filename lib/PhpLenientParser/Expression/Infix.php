<?php

namespace PhpLenientParser\Expression;

use PhpParser\Node;
use PhpLenientParser\ParserStateInterface;

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
        $right = $parser->getExpressionParser()->parse(
            $parser,
            $this->getPrecedence() - ($this->rightAssociative ? 1 : 0)
        );
        if ($right === null) {
            $right = $parser->getExpressionParser()->makeErrorNode($token);
        }

        $class = $this->getNodeClass();
        return $parser->setAttributes(new $class($left, $right), $left, $right);
    }
}
