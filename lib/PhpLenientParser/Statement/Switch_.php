<?php

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class Switch_ implements StatementInterface
{
    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();

        $parser->assert(ord('('));
        $condition = $parser->getExpressionParser()->parseOrError($parser);
        $parser->assert(ord(')'));

        $cases = [];
        if ($parser->eat(ord(':')) !== null) {
            $cases = $this->parseCases($parser, Tokens::T_ENDSWITCH);
            $parser->assert(Tokens::T_ENDSWITCH);
            $parser->assert(ord(';'));
        } elseif ($parser->eat(ord('{')) !== null) {
            $cases = $this->parseCases($parser, ord('{'));
            $parser->assert(ord('}'));
        }

        return $parser->setAttributes(new Node\Stmt\Switch_(
            $condition,
            $cases
        ), $token, $parser->last());
    }

    /**
     * @param ParserStateInterface $parser
     * @param int $delimiter
     *
     * @return Node\Stmt\Case_[]
     */
    public function parseCases(ParserStateInterface $parser, int $delimiter): array
    {
        $parser->eat(ord(';'));
        $cases = [];

        while (true) {
            $token = $parser->lookAhead();
            $condition = null;
            if ($token->type === Tokens::T_CASE) {
                $parser->eat();
                $condition = $parser->getExpressionParser()->parseOrError($parser);
            } elseif ($token->type === Tokens::T_DEFAULT) {
                $parser->eat();
            } else {
                break;
            }

            if ($parser->eat(ord(':')) === null) {
                $parser->eat(ord(';'));
            }

            $stmts = $parser->getStatementParser()->parseList($parser, $delimiter);
            $cases[] = $parser->setAttributes(new Node\Stmt\Case_(
                $condition,
                $stmts
            ), $token, $parser->last());
        }

        return $cases;
    }

    public function getToken()
    {
        return Tokens::T_SWITCH;
    }
}
