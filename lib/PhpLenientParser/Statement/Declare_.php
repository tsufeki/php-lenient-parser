<?php declare(strict_types=1);

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

    public function __construct(Identifier $identifierParser)
    {
        $this->identifierParser = $identifierParser;
    }

    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();
        $items = [];

        if ($parser->assert(ord('('))) {
            while (true) {
                $first = $parser->lookAhead();
                $id = $this->identifierParser->parse($parser);
                if ($id === null || !$parser->assert(ord('='))) {
                    break;
                }

                $expr = $parser->getExpressionParser()->parseOrError($parser);
                $items[] = new Node\Stmt\DeclareDeclare($id, $expr, $parser->getAttributes($first, $parser->last()));

                if ($parser->isNext(ord(')')) || !$parser->assert(ord(','))) {
                    break;
                }
            }
            $parser->assert(ord(')'));
        }

        $stmts = null;
        if ($parser->eatIf(ord(';')) !== null) {
            $stmts = null;
        } elseif ($parser->eatIf(ord(':')) !== null) {
            $stmts = $parser->getStatementParser()->parseList($parser, Tokens::T_ENDDECLARE);
            $parser->assert(Tokens::T_ENDDECLARE);
            $parser->assert(ord(';'));
        } else {
            $stmts = $parser->getStatementParser()->parse($parser) ?? [];
        }

        return new Node\Stmt\Declare_($items, $stmts, $parser->getAttributes($token, $parser->last()));
    }

    public function getToken(): ?int
    {
        return Tokens::T_DECLARE;
    }
}
