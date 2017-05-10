<?php

namespace PhpLenientParser\Statement;

use PhpLenientParser\Expression\Identifier;
use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class ClassConst implements StatementInterface
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
        $consts = [];

        while (true) {
            $first = $parser->lookAhead();
            $id = $this->identifierParser->parse($parser);
            if ($id === null) {
                break;
            }
            $expr = null;
            if ($parser->assert(ord('=')) !== null) {
                $expr = $parser->getExpressionParser()->parseOrError($parser);
            } else {
                $expr = $parser->getExpressionParser()->makeErrorNode($parser->last());
            }

            $consts[] = $parser->setAttributes(new Node\Const_($id, $expr), $first, $parser->last());
            if ($parser->lookAhead()->type === ord(';') || $parser->assert(ord(',')) === null) {
                break;
            }
        }

        $parser->assert(ord(';'));

        return $parser->setAttributes(new Node\Stmt\ClassConst($consts), $token, $parser->last());
    }

    public function getToken()
    {
        return Tokens::T_CONST;
    }
}
