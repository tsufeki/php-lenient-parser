<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class ArgumentList
{
    /**
     * @param ParserStateInterface $parser
     *
     * @return Node\Arg[]
     */
    public function parse(ParserStateInterface $parser): array
    {
        $parser->eat();
        $args = [];

        while (!$parser->isNext(ord(')'))) {
            $first = $parser->lookAhead();
            $ref = $parser->eat(ord('&')) !== null;
            $unpack = $parser->eat(Tokens::T_ELLIPSIS) !== null;
            $expr = $parser->getExpressionParser()->parse($parser);
            if ($expr === null) {
                if ($parser->isNext(ord(','), ord(')'))) {
                    $expr = $parser->getExpressionParser()->makeErrorNode($parser->last());
                } else {
                    break;
                }
            }

            $args[] = $parser->setAttributes(new Node\Arg($expr, $ref, $unpack), $first, $expr);
            $parser->eat(ord(','));
        }

        $parser->assert(ord(')'));

        return $args;
    }
}
