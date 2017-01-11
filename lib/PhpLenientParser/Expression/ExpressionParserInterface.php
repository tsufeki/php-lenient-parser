<?php

namespace PhpLenientParser\Expression;

use PhpParser\Node;
use PhpLenientParser\ParserStateInterface;
use PhpLenientParser\Token;

interface ExpressionParserInterface
{
    /**
     * @param ParserStateInterface $parser
     * @param int $precedence
     *
     * @return Node\Expr|null
     */
    public function parse(ParserStateInterface $parser, $precedence = 0);

    /**
     * @param Node|Token $last Node/token preceeding error.
     *
     * @return Node\Expr
     */
    public function makeErrorNode($last);
}
