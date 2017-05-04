<?php

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;

class StatementParser extends AbstractStatementParser
{
    /**
     * @var StatementInterface[]
     */
    private $statements = [];

    /**
     * @param StatementInterface[] $statements
     */
    public function __construct(StatementInterface ...$statements)
    {
        foreach ($statements as $statement) {
            $this->addStatement($statement);
        }
    }

    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->lookAhead();
        $stmt = null;
        if (isset($this->statements[$token->type])) {
            $stmt = $this->statements[$token->type]->parse($parser);
        }
        if ($stmt === null && isset($this->statements[null])) {
            $stmt = $this->statements[null]->parse($parser);
        }
        if ($stmt !== null && !is_array($stmt)) {
            $stmt = [$stmt];
        }

        return $stmt;
    }

    /**
     * @param StatementInterface $statement
     */
    public function addStatement(StatementInterface $statement)
    {
        $this->statements[$statement->getToken()] = $statement;
    }
}
