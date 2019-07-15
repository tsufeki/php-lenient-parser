<?php declare(strict_types=1);

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class Unset_ implements StatementInterface
{
    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();
        $parser->assert(ord('('));
        $expressions = $parser->getExpressionParser()->parseList($parser);
        $parser->assert(ord(')'));
        $parser->assert(ord(';'));

        return new Node\Stmt\Unset_($expressions, $parser->getAttributes($token, $parser->last()));
    }

    public function getToken(): ?int
    {
        return Tokens::T_UNSET;
    }
}
