<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class Assign extends AbstractInfix
{
    /**
     * @var int
     */
    private $refToken;

    /**
     * @var int
     */
    private $rightPrecedence;

    /**
     * @param int|null $rightPrecedence Precedence when binding to the right, defaults to $precedence.
     */
    public function __construct(
        int $token,
        int $refToken,
        int $precedence,
        ?int $rightPrecedence = null
    ) {
        parent::__construct($token, $precedence, self::RIGHT_ASSOCIATIVE);
        $this->refToken = $refToken;
        $this->rightPrecedence = ($rightPrecedence ?? $precedence) - 1;
    }

    public function parse(ParserStateInterface $parser, Node\Expr $left): ?Node\Expr
    {
        $token = $parser->eat();
        $isRef = $parser->eatIf($this->refToken) !== null;
        $right = $parser->getExpressionParser()->parseOrError($parser, $this->rightPrecedence);

        $class = $isRef ? Node\Expr\AssignRef::class : Node\Expr\Assign::class;
        /** @var Node\Expr */
        $node = new $class($left, $right);
        $parser->setAttributes($node, $left, $parser->last());

        return $node;
    }
}
