<?php

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class AggregateStatementParser implements StatementParserInterface
{
    /**
     * @var StatementParserInterface[]
     */
    private $parsers;

    public function __construct(StatementParserInterface ...$parsers)
    {
        $this->parsers = $parsers;
    }

    public function parse(ParserStateInterface $state)
    {
        foreach ($this->parsers as $parser) {
            $stmt = $parser->parse($state);
            if ($stmt !== null) {
                return $stmt;
            }
        }

        return null;
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
}
