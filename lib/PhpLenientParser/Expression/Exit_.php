<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class Exit_ extends AbstractPrefix
{
    public function __construct()
    {
        parent::__construct(Tokens::T_EXIT);
    }

    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();
        $kind = strtolower($token->value) === 'exit' ? Node\Expr\Exit_::KIND_EXIT : Node\Expr\Exit_::KIND_DIE;

        $expr = null;
        if ($parser->eat(ord('(')) !== null) {
            $expr = $parser->getExpressionParser()->parse($parser);
            $parser->assert(ord(')'));
        }

        return $parser->setAttributes(new Node\Expr\Exit_($expr, ['kind' => $kind]), $token, $parser->last());
    }
}
