<?php declare(strict_types=1);

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class DoWhile implements StatementInterface
{
    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();

        $stmts = $parser->getStatementParser()->parse($parser) ?: [];

        $condition = null;
        if ($parser->assert(Tokens::T_WHILE)) {
            $parser->assert(ord('('));
            $condition = $parser->getExpressionParser()->parseOrError($parser);
            $parser->assert(ord(')'));
            $parser->assert(ord(';'));
        } else {
            $condition = $parser->getExpressionParser()->makeErrorNode($parser->last());
        }

        return new Node\Stmt\Do_(
            $condition,
            $stmts,
            $parser->getAttributes($token, $parser->last())
        );
    }

    public function getToken(): ?int
    {
        return Tokens::T_DO;
    }
}
