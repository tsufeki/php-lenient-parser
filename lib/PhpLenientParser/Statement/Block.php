<?php

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;

class Block implements StatementInterface
{
    public function parse(ParserStateInterface $parser)
    {
        $parser->eat();
        $stmts = $parser->getStatementParser()->parseList($parser);
        $parser->assert(ord('}'));

        return $stmts;
    }

    public function getToken()
    {
        return ord('{');
    }
}
