<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class ArgumentList
{
    /**
     * @return Node\Arg[]
     */
    public function parse(ParserStateInterface $parser): array
    {
        $parser->eat();
        $args = [];

        while (!$parser->isNext(ord(')'))) {
            $first = $parser->lookAhead();
            $ref = $parser->eatIf(ord('&')) !== null;
            $unpack = $parser->eatIf(Tokens::T_ELLIPSIS) !== null;
            $expr = $parser->getExpressionParser()->parse($parser);
            if ($expr === null) {
                if ($parser->isNext(ord(','), ord(')'))) {
                    $expr = $parser->getExpressionParser()->makeErrorNode($parser->last());
                } else {
                    break;
                }
            }

            $arg = new Node\Arg($expr, $ref, $unpack);
            $parser->setAttributes($arg, $first, $parser->last());
            $parser->eatIf(ord(','));
            $args[] = $arg;
        }

        $parser->assert(ord(')'));

        return $args;
    }
}
