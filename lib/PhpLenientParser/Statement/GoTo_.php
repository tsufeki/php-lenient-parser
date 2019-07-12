<?php declare(strict_types=1);

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

    public function __construct(Identifier $identifierParser)
    {
        $this->identifierParser = $identifierParser;
    }

    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();
        if ($parser->isNext(Tokens::T_STRING)) {
            $label = $this->identifierParser->parse($parser);
            assert($label !== null);
        } else {
            $parser->unexpected($parser->lookAhead(), Tokens::T_STRING);
            $label = $this->identifierParser->makeEmpty($parser);
        }
        $parser->assert(ord(';'));
        $node = new Node\Stmt\Goto_($label);
        $parser->setAttributes($node, $token, $parser->last());

        return $node;
    }

    public function getToken(): ?int
    {
        return Tokens::T_GOTO;
    }
}
