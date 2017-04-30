<?php

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;
use PhpLenientParser\Expression\Identifier;

class GoTo_ implements StatementInterface
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
        $label = null;
        if ($parser->lookAhead()->type !== Tokens::T_STRING) {
            $errorNode = $parser->getExpressionParser()->makeErrorNode($parser->last());
            $label = $parser->setAttributes(new Node\Identifier(''), $errorNode, $errorNode);
        } else {
            $label = $this->identifierParser->parse($parser);
        }
        $parser->assert(ord(';'));

        return $parser->setAttributes(new Node\Stmt\Goto_($label), $token, $parser->last());
    }

    public function getToken()
    {
        return Tokens::T_GOTO;
    }
}
