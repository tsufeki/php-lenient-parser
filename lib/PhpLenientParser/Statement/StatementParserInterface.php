<?php

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

interface StatementParserInterface
{
    /**
     * @param ParserStateInterface $parser
     *
     * @return Node\Stmt[]|null
     */
    public function parse(ParserStateInterface $parser);

    /**
     * @param ParserStateInterface $parser
     * @param int[]                $delimiters Token expected after the list of statements,
     *                                         must not be a valid start of statement.
     *
     * @return Node\Stmt[]
     */
    public function parseList(ParserStateInterface $parser, int ...$delimiters): array;
}
