<?php declare(strict_types=1);

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
        if ($parser->eatIf(ord(':')) !== null) {
            $stmts = $parser->getStatementParser()->parseList($parser, Tokens::T_ENDFOR);
            $parser->assert(Tokens::T_ENDFOR);
            $parser->assert(ord(';'));
        } else {
            $stmts = $parser->getStatementParser()->parse($parser) ?: [];
        }

        $node = new Node\Stmt\For_([
            'init' => $init,
            'cond' => $cond,
            'loop' => $loop,
            'stmts' => $stmts,
        ]);
        $parser->setAttributes($node, $token, $parser->last());

        return $node;
    }

    public function getToken(): ?int
    {
        return Tokens::T_FOR;
    }
}
