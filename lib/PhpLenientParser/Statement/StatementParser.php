<?php

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;

class StatementParser implements StatementParserInterface
{
    /**
     * @var StatementInterface[]
     */
    private $statements = [];

    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->lookAhead();
        $tokenType = isset($this->statements[$token->type]) ? $token->type : null;
        $stmt = null;
        if (isset($this->statements[$tokenType])) {
            $stmt = $this->statements[$tokenType]->parse($parser);
        }
        if ($stmt !== null && !is_array($stmt)) {
            $stmt = [$stmt];
        }

        return $stmt;
    }

    public function parseList(ParserStateInterface $parser)
    {
        $stmts = [];
        while (null !== ($stmt = $this->parse($parser))) {
            $stmts = array_merge($stmts, $stmt);
        }

        return $stmts;
    }

    /**
     * @param StatementInterface $statement
     */
    public function addStatement(StatementInterface $statement)
    {
        $this->statements[$statement->getToken()] = $statement;
    }
}
