<?php declare(strict_types=1);

namespace PhpLenientParser\Statement;

use PhpLenientParser\Expression\Identifier;
use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

/**
 * Non-class const.
 */
class Const_ implements StatementInterface
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
        $consts = [];

        while (true) {
            if (!$parser->isNext(Tokens::T_STRING)) {
                break;
            }
            $first = $parser->lookAhead();
            $id = $this->identifierParser->parse($parser);
            assert($id !== null);
            $expr = null;
            if ($parser->assert(ord('='))) {
                $expr = $parser->getExpressionParser()->parseOrError($parser);
            } else {
                $expr = $parser->getExpressionParser()->makeErrorNode($parser->last());
            }

            $consts[] = new Node\Const_($id, $expr, $parser->getAttributes($first, $parser->last()));

            if ($parser->isNext(ord(';')) || !$parser->assert(ord(','))) {
                break;
            }
        }

        $parser->assert(ord(';'));

        return new Node\Stmt\Const_($consts, $parser->getAttributes($token, $parser->last()));
    }

    public function getToken(): ?int
    {
        return Tokens::T_CONST;
    }
}
