<?php declare(strict_types=1);

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

    public function __construct(
        int $token,
        int $secondToken,
        int $precedence,
        string $nodeClass,
        bool $rightAssociative = false
    ) {
        parent::__construct($token, $precedence, $nodeClass);
        $this->secondToken = $secondToken;
        $this->rightAssociative = $rightAssociative;
    }

    public function parse(ParserStateInterface $parser, Node\Expr $left): ?Node\Expr
    {
        $token = $parser->eat();
        $middle = $parser->getExpressionParser()->parse($parser);

        if ($parser->assert($this->secondToken)) {
            $right = $parser->getExpressionParser()->parseOrError(
                $parser,
                $this->getPrecedence() - ($this->rightAssociative ? 1 : 0)
            );
        } else {
            $right = $parser->getExpressionParser()->makeErrorNode($middle ?: $token);
        }

        $class = $this->getNodeClass();
        /** @var Node\Expr */
        $node = new $class($left, $middle, $right);
        $parser->setAttributes($node, $left, $right);

        return $node;
    }
}
