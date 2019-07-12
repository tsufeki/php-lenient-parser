<?php declare(strict_types=1);

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

interface StatementParserInterface
{
    /**
     * @return Node\Stmt[]|null
     */
    public function parse(ParserStateInterface $parser): ?array;

    /**
     * @param array<int,int> $delimiters Token expected after the list of statements,
     *                                   must not be a valid start of statement.
     *
     * @return Node\Stmt[]
     */
    public function parseList(ParserStateInterface $parser, int ...$delimiters): array;
}
