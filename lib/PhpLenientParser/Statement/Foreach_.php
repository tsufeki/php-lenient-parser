<?php declare(strict_types=1);

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

        if ($parser->eatIf(Tokens::T_AS) !== null) {
            $ref = $parser->eatIf(ord('&')) !== null;
            $var = $parser->getExpressionParser()->parse($parser);

            if ($parser->eatIf(Tokens::T_DOUBLE_ARROW) !== null) {
                $key = $var;
                $ref = $parser->eatIf(ord('&')) !== null;
                $var = $parser->getExpressionParser()->parse($parser);
            }
        }

        if ($var === null) {
            $var = $parser->getExpressionParser()->makeErrorNode($parser->last());
        }

        $parser->assert(ord(')'));

        $stmts = [];
        if ($parser->eatIf(ord(':')) !== null) {
            $stmts = $parser->getStatementParser()->parseList($parser, Tokens::T_ENDFOREACH);
            $parser->assert(Tokens::T_ENDFOREACH);
            $parser->assert(ord(';'));
        } else {
            $stmts = $parser->getStatementParser()->parse($parser) ?: [];
        }

        return new Node\Stmt\Foreach_($expr, $var, [
            'keyVar' => $key,
            'byRef' => $ref,
            'stmts' => $stmts,
        ], $parser->getAttributes($token, $parser->last()));
    }

    public function getToken(): ?int
    {
        return Tokens::T_FOREACH;
    }
}
