<?php

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

abstract class AbstractStatementParser implements StatementParserInterface
{
    public function parseList(ParserStateInterface $parser, int ...$delimiters): array
    {
        $delimiters[] = 0; // EOF.
        $stmts = [];
        while (null !== ($stmt = $this->parse($parser))) {
            $stmts = array_merge($stmts, $stmt);
        }

        $lookAhead = $parser->lookAhead();
        if (in_array($lookAhead->type, $delimiters)) {
            if (!empty($lookAhead->startAttributes['comments'])) {
                $stmts[] = $parser->setAttributes(new Node\Stmt\Nop(), $lookAhead, $lookAhead);
            }
        }

        return $stmts;
    }
}
