<?php declare(strict_types=1);

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
                $attrs = $parser->getAttributes($lookAhead, $lookAhead);
                if (isset($attrs['startFilePos'])) {
                    $attrs['endFilePos'] = $attrs['startFilePos'] - 1;
                }
                if (isset($attrs['startTokenPos'])) {
                    $attrs['endTokenPos'] = $attrs['startTokenPos'] - 1;
                }

                $stmts[] = new Node\Stmt\Nop($attrs);
            }
        }

        return $stmts;
    }
}
