<?php

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
        while (null !== ($first = $parser->eat(Tokens::T_ELSEIF))) {
            list($condition, $stmts, $colon) = $this->parseConditionBlock($parser);
            $elseIfs[] = $parser->setAttributes(new Node\Stmt\ElseIf_($condition, $stmts), $first, $parser->last());
        }

        $else = null;
        if (null !== ($first = $parser->eat(Tokens::T_ELSE))) {
            list($stmts, $colon) = $this->parseBlock($parser);
            $else = $parser->setAttributes(new Node\Stmt\Else_($stmts), $first, $parser->last());
        }

        if ($colon) {
            $parser->assert(Tokens::T_ENDIF);
            $parser->assert(ord(';'));
        }

        return $parser->setAttributes(new Node\Stmt\If_(
            $ifCondition,
            [
                'stmts' => $ifStmts,
                'elseifs' => $elseIfs,
                'else' => $else,
            ]
        ), $token, $parser->last());
    }

    /**
     * @param ParserStateInterface $parser
     *
     * @return array
     */
    private function parseBlock(ParserStateInterface $parser)
    {
        $stmts = [];
        $colon = false;
        if ($parser->eat(ord(':')) !== null) {
            $stmts = $parser->getStatementParser()->parseList($parser);
            $colon = true;
        } else {
            $stmts = $parser->getStatementParser()->parse($parser) ?: [];
        }

        return [$stmts, $colon];
    }

    /**
     * @param ParserStateInterface $parser
     *
     * @return array
     */
    private function parseConditionBlock(ParserStateInterface $parser)
    {
        $parser->assert(ord('('));
        $condition = $parser->getExpressionParser()->parseOrError($parser);
        $parser->assert(ord(')'));

        list($stmts, $colon) = $this->parseBlock($parser);
        return [$condition, $stmts, $colon];
    }

    public function getToken()
    {
        return Tokens::T_IF;
    }
}
