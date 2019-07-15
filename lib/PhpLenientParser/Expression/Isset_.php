<?php declare(strict_types=1);

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

    public function parse(ParserStateInterface $parser): ?Node\Expr
    {
        $token = $parser->eat();

        $args = [];
        $parser->assert(ord('('));

        while (!$parser->isNext(ord(')'))) {
            $first = $parser->lookAhead();
            $expr = $parser->getExpressionParser()->parse($parser);
            if ($expr === null) {
                if ($parser->isNext(ord(','), ord(')'))) {
                    $expr = $parser->getExpressionParser()->makeErrorNode($parser->last());
                } else {
                    break;
                }
            }

            $args[] = $expr;
            $parser->eatIf(ord(','));
        }

        $parser->assert(ord(')'));

        return new Node\Expr\Isset_($args, $parser->getAttributes($token, $parser->last()));
    }
}
