<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class ArrayIndex extends AbstractOperator implements InfixInterface
{
    /**
     * @var int
     */
    private $closeToken;

    public function __construct(int $token, int $closeToken, int $precedence)
    {
        parent::__construct($token, $precedence, Node\Expr\ArrayDimFetch::class);
        $this->closeToken = $closeToken;
    }

    public function parse(ParserStateInterface $parser, Node\Expr $left): ?Node\Expr
    {
        $token = $parser->eat();
        $right = $parser->getExpressionParser()->parse($parser);
        $parser->assert($this->closeToken);

        $class = $this->getNodeClass();
        /** @var Node\Expr */
        $node = new $class($left, $right);
        $parser->setAttributes($node, $left, $parser->last());

        return $node;
    }
}
