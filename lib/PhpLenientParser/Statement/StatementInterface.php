<?php

namespace PhpLenientParser\Statement;

use PhpParser\Node;
use PhpLenientParser\ParserStateInterface;

interface StatementInterface
{
    /**
     * @param ParserStateInterface $parser
     *
     * @return Node\Stmt|null
     */
    public function parse(ParserStateInterface $parser);

    /**
     * @return int
     */
    public function getToken();
}
