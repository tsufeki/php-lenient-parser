<?php

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;

class Nop implements StatementInterface
{
    public function parse(ParserStateInterface $parser)
    {
        $parser->eat();

        return [];
    }

    public function getToken()
    {
        return ord(';');
    }
}
