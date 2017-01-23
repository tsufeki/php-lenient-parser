<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class Include_ extends AbstractPrefix
{
    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();

        $kind = null;
        switch ($token->type) {
            case Tokens::T_INCLUDE:
                $kind = Node\Expr\Include_::TYPE_INCLUDE;
                break;
            case Tokens::T_INCLUDE_ONCE:
                $kind = Node\Expr\Include_::TYPE_INCLUDE_ONCE;
                break;
            case Tokens::T_REQUIRE:
                $kind = Node\Expr\Include_::TYPE_REQUIRE;
                break;
            case Tokens::T_REQUIRE_ONCE:
                $kind = Node\Expr\Include_::TYPE_REQUIRE_ONCE;
                break;
        }

        $expr = $parser->getExpressionParser()->parseOrError($parser);

        return $parser->setAttributes(new Node\Expr\Include_($expr, $kind), $token, $parser->last());
    }
}
