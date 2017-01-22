<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class Yield_ extends AbstractPrefix
{
    public function __construct()
    {
        parent::__construct(Tokens::T_YIELD);
    }

    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();
        $key = null;
        $expr = $parser->getExpressionParser()->parse($parser);

        if ($parser->eat(Tokens::T_DOUBLE_ARROW) !== null) {
            $key = $expr;
            $expr = $parser->getExpressionParser()->parse($parser);
            if ($expr === null) {
                $expr = $parser->getExpressionParser()->makeErrorNode($parser->last());
            }
        }

        return $parser->setAttributes(new Node\Expr\Yield_($expr, $key), $token, $parser->last());
    }
}
