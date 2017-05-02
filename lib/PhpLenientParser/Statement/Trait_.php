<?php

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;
use PhpLenientParser\Expression\Identifier;

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

    /**
     * @param Identifier $identifierParser
     * @param StatementParserInterface $classStatementsParser
     */
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

        $stmts = [];
        if ($parser->assert(ord('{')) !== null) {
            $stmts = $this->classStatementsParser->parseList($parser, ord('}'));
            $parser->assert(ord('}'));
        }

        return $parser->setAttributes(new Node\Stmt\Trait_($id, ['stmts' => $stmts]), $token, $parser->last());
    }

    public function getToken()
    {
        return Tokens::T_TRAIT;
    }
}
