<?php

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class MemberModifier implements StatementInterface
{
    /**
     * @var int
     */
    private $token;

    /**
     * @var int
     */
    private $modifier;

    /**
     * @var StatementParserInterface
     */
    private $classStatementsParser;

    public function __construct(int $token, int $modifier, StatementParserInterface $classStatementsParser)
    {
        $this->token = $token;
        $this->modifier = $modifier;
        $this->classStatementsParser = $classStatementsParser;
    }

    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();
        /** @var Node\Stmt\ClassConst[]|Node\Stmt\ClassMethod[]|Node\Stmt\Property */
        $stmts = $this->classStatementsParser->parse($parser);
        if (empty($stmts)) {
            return null;
        }

        $stmts[0]->flags |= $this->modifier;

        return $parser->setAttributes($stmts[0], $token, $stmts[0]);
    }

    public function getToken()
    {
        return $this->token;
    }
}
