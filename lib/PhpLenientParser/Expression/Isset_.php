<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class Isset_ extends AbstractPrefix
{
    public function __construct()
    {
        parent::__construct(Tokens::T_ISSET);
    }

    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();

        $args = [];
        $parser->assert(ord('('));

        while ($parser->lookAhead()->type !== ord(')')) {
            $first = $parser->lookAhead();
            $expr = $parser->getExpressionParser()->parse($parser);
            if ($expr === null) {
                if (in_array($parser->lookAhead()->type, [ord(','), ord(')')])) {
                    $expr = $parser->getExpressionParser()->makeErrorNode($parser->last());
                } else {
                    break;
                }
            }

            $args[] = $expr;
            $parser->eat(ord(','));
        }

        $parser->assert(ord(')'));

        return $parser->setAttributes(new Node\Expr\Isset_($args), $token, $parser->last());
    }
}
