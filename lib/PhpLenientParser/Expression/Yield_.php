<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class Yield_ extends AbstractPrefix
{
    /**
     * @var int
     */
    private $precedence;

    public function __construct(int $precedence = 0)
    {
        parent::__construct(Tokens::T_YIELD);
        $this->precedence = $precedence;
    }

    public function parse(ParserStateInterface $parser): ?Node\Expr
    {
        $token = $parser->eat();
        $key = null;
        $expr = $parser->getExpressionParser()->parse($parser, $this->precedence);

        if ($parser->eatIf(Tokens::T_DOUBLE_ARROW) !== null) {
            $key = $expr;
            $expr = $parser->getExpressionParser()->parseOrError($parser, $this->precedence);
        }

        $node = new Node\Expr\Yield_($expr, $key);
        $parser->setAttributes($node, $token, $parser->last());

        return $node;
    }
}
