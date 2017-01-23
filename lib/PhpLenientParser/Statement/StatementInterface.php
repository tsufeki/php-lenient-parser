<?php

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

interface StatementInterface
{
    /**
     * @param ParserStateInterface $parser
     *
     * @return Node\Stmt|Node\Stmt[]|null
     */
    public function parse(ParserStateInterface $parser);

    /**
     * @return int|null
     */
    public function getToken();
}
