<?php declare(strict_types=1);

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
     * @var int
     */
    private $rightPrecedence;

    /**
     * @param int|null $rightPrecedence Precedence when binding to the right, defaults to $precedence.
     */
    public function __construct(int $token, int $refToken, int $precedence, ?int $rightPrecedence = null)
    {
        parent::__construct($token, $precedence, Expr\Assign::class);
        $this->refToken = $refToken;
        $this->refNodeClass = Expr\AssignRef::class;
        $this->rightPrecedence = ($rightPrecedence ?? $precedence) - 1;
    }

    public function parse(ParserStateInterface $parser, Node\Expr $left): ?Node\Expr
    {
        $token = $parser->eat();
        $isRef = $parser->eatIf($this->refToken) !== null;
        $right = $parser->getExpressionParser()->parseOrError($parser, $this->rightPrecedence);

        $class = $isRef ? $this->refNodeClass : $this->getNodeClass();
        /** @var Node\Expr */
        $node = new $class($left, $right);
        $parser->setAttributes($node, $left, $right);

        return $node;
    }
}
