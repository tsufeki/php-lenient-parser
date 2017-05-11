<?php

namespace PhpLenientParser\Statement;

use PhpLenientParser\Expression\Identifier;
use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

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
        if ($parser->isNext(Tokens::T_STRING)) {
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
