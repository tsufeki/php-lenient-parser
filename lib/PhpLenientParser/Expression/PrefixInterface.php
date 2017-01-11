<?php

namespace PhpLenientParser\Expression;

use PhpParser\Node;
use PhpLenientParser\ParserStateInterface;

interface PrefixInterface
{
    /**
     * @param ParserStateInterface $parser
     *
     * @return Node\Expr|null
     */
    public function parse(ParserStateInterface $parser);

    /**
     * @return int
     */
    public function getToken();
}
