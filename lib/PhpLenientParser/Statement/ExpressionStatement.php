<?php

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;

class ExpressionStatement implements StatementInterface
{
    public function parse(ParserStateInterface $parser)
    {
        $stmt = $parser->getExpressionParser()->parse($parser);
        $parser->assert(ord(';'));

        return $stmt;
    }

    public function getToken()
    {
        return null;
    }
}
