<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

interface InfixInterface
{
    /**
     * @param ParserStateInterface $parser
     * @param Node\Expr            $left
     *
     * @return Node\Expr|null
     */
    public function parse(ParserStateInterface $parser, Node $left);

    /**
     * @return int
     */
    public function getToken(): int;

    /**
     * @return int
     */
    public function getPrecedence(): int;
}
