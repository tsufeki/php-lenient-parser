<?php

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;
use PhpLenientParser\Expression\Identifier;

class Label implements StatementInterface
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
        if ($parser->lookAhead(1)->type !== ord(':')) {
            return null;
        }

        $token = $parser->lookAhead();
        $id = $this->identifierParser->parse($parser);
        $parser->eat();

        return $parser->setAttributes(new Node\Stmt\Label($id), $token, $parser->last());
    }

    public function getToken()
    {
        return Tokens::T_STRING;
    }
}
