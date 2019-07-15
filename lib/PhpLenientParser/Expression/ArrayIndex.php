<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class ArrayIndex extends AbstractInfix
{
    /**
     * @var int
     */
    private $closeToken;

    public function __construct(int $token, int $closeToken, int $precedence)
    {
        parent::__construct($token, $precedence, self::LEFT_ASSOCIATIVE);
        $this->closeToken = $closeToken;
    }

    public function parse(ParserStateInterface $parser, Node\Expr $left): ?Node\Expr
    {
        $token = $parser->eat();
        $right = $parser->getExpressionParser()->parse($parser);
        $parser->assert($this->closeToken);

        return new Node\Expr\ArrayDimFetch($left, $right, $parser->getAttributes($left, $parser->last()));
    }
}
