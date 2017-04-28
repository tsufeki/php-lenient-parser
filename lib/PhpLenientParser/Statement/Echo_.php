<?php

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class Echo_ implements StatementInterface
{
    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();
        $expressions = $parser->getExpressionParser()->parseList($parser);
        $parser->assert(ord(';'));

        return $parser->setAttributes(new Node\Stmt\Echo_($expressions), $token, $parser->last());
    }

    public function getToken()
    {
        return Tokens::T_ECHO;
    }
}
