<?php declare(strict_types=1);

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
        if ($parser->eatIf(ord(':')) !== null) {
            $stmts = $parser->getStatementParser()->parseList($parser, Tokens::T_ENDWHILE);
            $parser->assert(Tokens::T_ENDWHILE);
            $parser->assert(ord(';'));
        } else {
            $stmts = $parser->getStatementParser()->parse($parser) ?: [];
        }

        return new Node\Stmt\While_($condition, $stmts, $parser->getAttributes($token, $parser->last()));
    }

    public function getToken(): ?int
    {
        return Tokens::T_WHILE;
    }
}
