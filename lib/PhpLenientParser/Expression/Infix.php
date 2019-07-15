<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class Infix extends AbstractInfix
{
    /**
     * @var int
     */
    private $rightPrecedence;

    /**
     * @var string
     */
    private $nodeClass;

    /**
     * @param int|null $rightPrecedence Precedence when binding to the right, defaults to $precedence.
     */
    public function __construct(
        int $token,
        int $precedence,
        string $nodeClass,
        int $associativity = self::LEFT_ASSOCIATIVE,
        ?int $rightPrecedence = null
    ) {
        parent::__construct($token, $precedence, $associativity);
        $this->rightPrecedence = ($rightPrecedence ?? $precedence) - ($associativity === self::RIGHT_ASSOCIATIVE ? 1 : 0);
        $this->nodeClass = $nodeClass;
    }

    public function parse(ParserStateInterface $parser, Node\Expr $left): ?Node\Expr
    {
        $token = $parser->eat();
        $right = $parser->getExpressionParser()->parseOrError($parser, $this->rightPrecedence);

        /** @var Node\Expr */
        $node = new $this->nodeClass($left, $right, $parser->getAttributes($left, $parser->last()));

        return $node;
    }
}
