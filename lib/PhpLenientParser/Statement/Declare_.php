<?php

namespace PhpLenientParser\Statement;

use PhpLenientParser\Expression\Identifier;
use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class Declare_ implements StatementInterface
{
    /**
     * @var Identifier
     */
    private $identifierParser;

    /**
     * @param Identifier $identifierParser
     */
    public function __construct(Identifier $identifierParser)
    {
        $this->identifierParser = $identifierParser;
    }

    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();
        $items = [];

        if ($parser->assert(ord('(')) !== null) {
            while (true) {
                $first = $parser->lookAhead();
                $id = $this->identifierParser->parse($parser);
                if ($id === null || $parser->assert(ord('=')) === null) {
                    break;
                }

                $expr = $parser->getExpressionParser()->parseOrError($parser);
                $items[] = $parser->setAttributes(new Node\Stmt\DeclareDeclare($id, $expr), $first, $parser->last());

                if ($parser->lookAhead()->type === ord(')') || $parser->assert(ord(',')) === null) {
                    break;
                }
            }
            $parser->assert(ord(')'));
        }

        $stmts = null;
        if ($parser->eat(ord(';')) !== null) {
            $stmts = null;
        } elseif ($parser->eat(ord(':')) !== null) {
            $stmts = $parser->getStatementParser()->parseList($parser, Tokens::T_ENDDECLARE);
            $parser->assert(Tokens::T_ENDDECLARE);
            $parser->assert(ord(';'));
        } else {
            $stmts = $parser->getStatementParser()->parse($parser) ?? [];
        }

        return $parser->setAttributes(new Node\Stmt\Declare_($items, $stmts), $token, $parser->last());
    }

    public function getToken()
    {
        return Tokens::T_DECLARE;
    }
}
