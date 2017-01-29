<?php

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class While_ implements StatementInterface
{
    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();

        $parser->assert(ord('('));
        $condition = $parser->getExpressionParser()->parseOrError($parser);
        $parser->assert(ord(')'));

        $stmts = [];
        if ($parser->eat(ord(':')) !== null) {
            $stmts = $parser->getStatementParser()->parseList($parser);
            $parser->assert(Tokens::T_ENDWHILE);
            $parser->assert(ord(';'));
        } else {
            $stmt = $parser->getStatementParser()->parse($parser);
            $stmts = $stmt === null ? [] : [$stmt];
        }

        return $parser->setAttributes(new Node\Stmt\While_(
            $condition,
            $stmts
        ), $token, $parser->last());
    }

    public function getToken()
    {
        return Tokens::T_WHILE;
    }
}
