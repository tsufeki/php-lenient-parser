<?php declare(strict_types=1);

namespace PhpLenientParser\Statement;

use PhpLenientParser\Expression\Identifier;
use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

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
        assert($id !== null);
        $parser->eat();

        return new Node\Stmt\Label($id, $parser->getAttributes($token, $parser->last()));
    }

    public function getToken(): ?int
    {
        return Tokens::T_STRING;
    }
}
