<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

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
    public function getToken(): int;
}
