<?php

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

interface StatementParserInterface
{
    /**
     * @param ParserStateInterface $parser
     *
     * @return Node\Stmt|null
     */
    public function parse(ParserStateInterface $parser);
}
