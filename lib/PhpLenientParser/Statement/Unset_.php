<?php

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

        return $parser->setAttributes(new Node\Stmt\Unset_($expressions), $token, $parser->last());
    }

    public function getToken()
    {
        return Tokens::T_UNSET;
    }
}
