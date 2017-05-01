<?php

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class Nop implements StatementInterface
{
    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();
        $stmts = [];
        if (!empty($token->startAttributes['comments'])) {
            $stmts[] = $parser->setAttributes(new Node\Stmt\Nop(), $token, $token);
        }

        return $stmts;
    }

    public function getToken()
    {
        return ord(';');
    }
}
