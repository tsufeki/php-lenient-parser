<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class Ternary extends AbstractOperator implements InfixInterface
{
    /**
     * @var int
     */
    private $secondToken;

    /**
     * @var bool
     */
    private $rightAssociative;

    /**
     * @param int $token
     * @param int $secondToken
     * @param int $precedence
     * @param string $nodeClass
     * @param bool $rightAssociative
     */
    public function __construct($token, $secondToken, $precedence, $nodeClass, $rightAssociative = false)
    {
        parent::__construct($token, $precedence, $nodeClass);
        $this->secondToken = $secondToken;
        $this->rightAssociative = $rightAssociative;
    }

    public function parse(ParserStateInterface $parser, Node $left)
    {
        $token = $parser->eat();
        $middle = $parser->getExpressionParser()->parse($parser);

        $secondToken = $parser->assert($this->secondToken);
        if ($secondToken !== null) {
            $right = $parser->getExpressionParser()->parseOrError(
                $parser,
                $this->getPrecedence() - ($this->rightAssociative ? 1 : 0)
            );
        } else {
            $right = $parser->getExpressionParser()->makeErrorNode($middle ?: $token);
        }

        $class = $this->getNodeClass();
        return $parser->setAttributes(new $class($left, $middle, $right), $left, $right);
    }
}
