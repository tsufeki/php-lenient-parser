<?php

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class ExpressionStatement implements StatementInterface
{
    public function parse(ParserStateInterface $parser)
    {
        $expr = $parser->getExpressionParser()->parse($parser);
        $stmt = null;
        if ($expr !== null) {
            $parser->assert(ord(';'));
            $stmt = new Node\Stmt\Expression($expr);
            $parser->setAttributes($stmt, $expr, $expr);
        }

        return $stmt;
    }

    public function getToken()
    {
        return null;
    }
}
