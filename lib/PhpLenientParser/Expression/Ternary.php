<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class Ternary extends AbstractInfix
{
    /**
     * @var int
     */
    private $secondToken;

    public function __construct(
        int $token,
        int $secondToken,
        int $precedence
    ) {
        parent::__construct($token, $precedence, self::LEFT_ASSOCIATIVE);
        $this->secondToken = $secondToken;
    }

    public function parse(ParserStateInterface $parser, Node\Expr $left): ?Node\Expr
    {
        $token = $parser->eat();
        $middle = $parser->getExpressionParser()->parse($parser);

        if ($parser->assert($this->secondToken)) {
            $right = $parser->getExpressionParser()->parseOrError($parser, $this->getPrecedence());
        } else {
            $right = $parser->getExpressionParser()->makeErrorNode($middle ?: $token);
        }

        $node = new Node\Expr\Ternary($left, $middle, $right);
        $parser->setAttributes($node, $left, $parser->last());

        return $node;
    }
}
