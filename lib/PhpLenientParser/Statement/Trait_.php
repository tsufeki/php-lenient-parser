<?php declare(strict_types=1);

namespace PhpLenientParser\Statement;

use PhpLenientParser\Expression\Identifier;
use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class Trait_ implements StatementInterface
{
    /**
     * @var Identifier
     */
    private $identifierParser;

    /**
     * @var StatementParserInterface
     */
    private $classStatementsParser;

    public function __construct(Identifier $identifierParser, StatementParserInterface $classStatementsParser)
    {
        $this->identifierParser = $identifierParser;
        $this->classStatementsParser = $classStatementsParser;
    }

    public function parse(ParserStateInterface $parser)
    {
        if ($parser->lookAhead(1)->type !== Tokens::T_STRING) {
            return null;
        }

        $token = $parser->eat();
        $id = $this->identifierParser->parse($parser);
        assert($id !== null);

        $stmts = [];
        if ($parser->assert(ord('{'))) {
            $stmts = $this->classStatementsParser->parseList($parser, ord('}'));
            $parser->assert(ord('}'));
        }

        $this->identifierParser->checkClassName($parser, $id);

        return new Node\Stmt\Trait_($id, ['stmts' => $stmts], $parser->getAttributes($token, $parser->last()));
    }

    public function getToken(): ?int
    {
        return Tokens::T_TRAIT;
    }
}
