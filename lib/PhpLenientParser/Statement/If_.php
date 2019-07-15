<?php declare(strict_types=1);

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class If_ implements StatementInterface
{
    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();
        list($ifCondition, $ifStmts, $colon) = $this->parseConditionBlock($parser);

        $elseIfs = [];
        while (null !== ($first = $parser->eatIf(Tokens::T_ELSEIF))) {
            list($condition, $stmts, $colon) = $this->parseConditionBlock($parser);
            $elseIfs[] = new Node\Stmt\ElseIf_($condition, $stmts, $parser->getAttributes($first, $parser->last()));
        }

        $else = null;
        if (null !== ($first = $parser->eatIf(Tokens::T_ELSE))) {
            list($stmts, $colon) = $this->parseBlock($parser);
            $else = new Node\Stmt\Else_($stmts, $parser->getAttributes($first, $parser->last()));
        }

        if ($colon) {
            $parser->assert(Tokens::T_ENDIF);
            $parser->assert(ord(';'));
        }

        return new Node\Stmt\If_($ifCondition, [
            'stmts' => $ifStmts,
            'elseifs' => $elseIfs,
            'else' => $else,
        ], $parser->getAttributes($token, $parser->last()));
    }

    /**
     * @return array{0: Node\Stmt[], 1: bool}
     */
    private function parseBlock(ParserStateInterface $parser): array
    {
        $stmts = [];
        $colon = false;
        if ($parser->eatIf(ord(':')) !== null) {
            $stmts = $parser->getStatementParser()->parseList($parser, Tokens::T_ENDIF);
            $colon = true;
        } else {
            $stmts = $parser->getStatementParser()->parse($parser) ?: [];
        }

        return [$stmts, $colon];
    }

    /**
     * @return array{0: Node\Expr, 1: Node\Stmt[], 2: bool}
     */
    private function parseConditionBlock(ParserStateInterface $parser): array
    {
        $parser->assert(ord('('));
        $condition = $parser->getExpressionParser()->parseOrError($parser);
        $parser->assert(ord(')'));

        list($stmts, $colon) = $this->parseBlock($parser);

        return [$condition, $stmts, $colon];
    }

    public function getToken(): ?int
    {
        return Tokens::T_IF;
    }
}
