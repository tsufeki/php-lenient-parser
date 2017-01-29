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
        $init = $this->parseExpressionList($parser);
        $parser->assert(ord(';'));
        $cond = $this->parseExpressionList($parser);
        $parser->assert(ord(';'));
        $loop = $this->parseExpressionList($parser);
        $parser->assert(ord(')'));

        $stmts = [];
        if ($parser->eat(ord(':')) !== null) {
            $stmts = $parser->getStatementParser()->parseList($parser);
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

    /**
     * @param ParserStateInterface $parser
     *
     * @return Node\Expr[]
     */
    private function parseExpressionList(ParserStateInterface $parser)
    {
        $expressions = [];
        while (true) {
            $expr = $parser->getExpressionParser()->parse($parser);
            if ($expr !== null) {
                $expressions[] = $expr;
            }
            if ($parser->eat(ord(',')) === null) {
                break;
            }
        }

        return $expressions;
    }

    public function getToken()
    {
        return Tokens::T_FOR;
    }
}
