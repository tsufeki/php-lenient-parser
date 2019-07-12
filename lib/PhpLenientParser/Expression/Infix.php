<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class Infix extends AbstractOperator implements InfixInterface
{
    /**
     * @var int
     */
    private $rightPrecedence;

    public function __construct(
        int $token,
        int $precedence,
        string $nodeClass,
        bool $rightAssociative = false,
        ?int $rightPrecedence = null
    ) {
        parent::__construct($token, $precedence, $nodeClass);
        $this->rightPrecedence = ($rightPrecedence ?? $precedence) - ($rightAssociative ? 1 : 0);
    }

    public function parse(ParserStateInterface $parser, Node\Expr $left): ?Node\Expr
    {
        $token = $parser->eat();
        $right = $parser->getExpressionParser()->parseOrError($parser, $this->rightPrecedence);

        $class = $this->getNodeClass();
        /** @var Node\Expr */
        $node = new $class($left, $right);
        $parser->setAttributes($node, $left, $right);

        return $node;
    }
}
