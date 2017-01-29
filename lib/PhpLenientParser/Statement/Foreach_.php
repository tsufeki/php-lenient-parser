<?php

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class Foreach_ implements StatementInterface
{
    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();
        $parser->assert(ord('('));

        $expr = $parser->getExpressionParser()->parseOrError($parser);
        $key = null;
        $ref = false;
        $var = null;

        if ($parser->eat(Tokens::T_AS) !== null) {
            $ref = $parser->eat(ord('&')) !== null;
            $var = $parser->getExpressionParser()->parse($parser);

            if ($parser->eat(Tokens::T_DOUBLE_ARROW) !== null) {
                $key = $var;
                $ref = $parser->eat(ord('&')) !== null;
                $var = $parser->getExpressionParser()->parse($parser);
            }
        }

        if ($var === null) {
            $var = $parser->getExpressionParser()->makeErrorNode($parser->last());
        }

        $parser->assert(ord(')'));

        $stmts = [];
        if ($parser->eat(ord(':')) !== null) {
            $stmts = $parser->getStatementParser()->parseList($parser);
            $parser->assert(Tokens::T_ENDFOREACH);
            $parser->assert(ord(';'));
        } else {
            $stmts = $parser->getStatementParser()->parse($parser) ?: [];
        }

        return $parser->setAttributes(new Node\Stmt\Foreach_(
            $expr,
            $var,
            [
                'keyVar' => $key,
                'byRef' => $ref,
                'stmts' => $stmts,
            ]
        ), $token, $parser->last());
    }

    public function getToken()
    {
        return Tokens::T_FOREACH;
    }
}
