<?php

namespace PhpLenientParser\Expression;

use PhpParser\Node;
use PhpLenientParser\ParserStateInterface;

interface InfixInterface
{
    /**
     * @param ParserStateInterface $parser
     * @param Node\Expr $left
     *
     * @return Node\Expr|null
     */
    public function parse(ParserStateInterface $parser, Node $left);

    /**
     * @return int
     */
    public function getToken();

    /**
     * @return int
     */
    public function getPrecedence();
}
