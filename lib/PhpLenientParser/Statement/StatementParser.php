<?php

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class StatementParser implements StatementParserInterface
{
    /**
     * @var StatementInterface[]
     */
    private $statements = [];

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

    public function parseList(ParserStateInterface $parser, int $delimiter = null): array
    {
        $stmts = [];
        while (null !== ($stmt = $this->parse($parser))) {
            $stmts = array_merge($stmts, $stmt);
        }

        $lookAhead = $parser->lookAhead();
        if ($lookAhead->type === 0 || ($delimiter !== null && $lookAhead->type === $delimiter)) {
            if (!empty($lookAhead->startAttributes['comments'])) {
                $stmts[] = $parser->setAttributes(new Node\Stmt\Nop(), $lookAhead, $parser->last());
            }
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
