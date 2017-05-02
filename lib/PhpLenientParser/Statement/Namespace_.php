<?php

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;
use PhpLenientParser\Expression\Name;

class Namespace_ implements StatementInterface
{
    /**
     * @var Name
     */
    private $nameParser;

    /**
     * @var StatementParserInterface
     */
    private $innerStatementsParser;

    /**
     * @param Name $nameParser
     * @param StatementParserInterface $innerStatementsParser
     */
    public function __construct(Name $nameParser, StatementParserInterface $innerStatementsParser)
    {
        $this->nameParser = $nameParser;
        $this->innerStatementsParser = $innerStatementsParser;
    }

    public function parse(ParserStateInterface $parser)
    {
        if (!in_array($parser->lookAhead(1)->type, [ord('{'), Tokens::T_STRING])) {
            return null;
        }

        $token = $parser->eat();
        $name = $this->nameParser->parserOrNull($parser);

        $stmts = [];
        if ($parser->eat(ord('{')) !== null) {
            $stmts = $this->innerStatementsParser->parseList($parser, ord('}'));
            $parser->assert(ord('}'));
        } else {
            $parser->assert(ord(';'));
            $stmts = $this->innerStatementsParser->parseList($parser, Tokens::T_NAMESPACE);
        }

        return $parser->setAttributes(new Node\Stmt\Namespace_($name, $stmts), $token, $parser->last());
    }

    public function getToken()
    {
        return Tokens::T_NAMESPACE;
    }
}
