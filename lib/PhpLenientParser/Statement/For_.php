<?php

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class For_ implements StatementInterface
{
    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();

        $parser->assert(ord('('));
        $init = $parser->getExpressionParser()->parseList($parser);
        $parser->assert(ord(';'));
        $cond = $parser->getExpressionParser()->parseList($parser);
        $parser->assert(ord(';'));
        $loop = $parser->getExpressionParser()->parseList($parser);
        $parser->assert(ord(')'));

        $stmts = [];
        if ($parser->eat(ord(':')) !== null) {
            $stmts = $parser->getStatementParser()->parseList($parser, Tokens::T_ENDFOR);
            $parser->assert(Tokens::T_ENDFOR);
            $parser->assert(ord(';'));
        } else {
            $stmts = $parser->getStatementParser()->parse($parser) ?: [];
        }

        return $parser->setAttributes(new Node\Stmt\For_([
            'init' => $init,
            'cond' => $cond,
            'loop' => $loop,
            'stmts' => $stmts,
        ]), $token, $parser->last());
    }

    public function getToken()
    {
        return Tokens::T_FOR;
    }
}
