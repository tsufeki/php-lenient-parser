<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpLenientParser\Token;
use PhpParser\Node;

interface ExpressionParserInterface
{
    /**
     * @param ParserStateInterface $parser
     * @param int                  $precedence
     *
     * @return Node\Expr|null
     */
    public function parse(ParserStateInterface $parser, int $precedence = 0);

    /**
     * @param ParserStateInterface $parser
     * @param int                  $precedence
     *
     * @return Node\Expr
     */
    public function parseOrError(ParserStateInterface $parser, int $precedence = 0);

    /**
     * @param ParserStateInterface $parser
     *
     * @return Node\Expr[]
     */
    public function parseList(ParserStateInterface $parser): array;

    /**
     * @param Node|Token $last Node/token preceeding error.
     *
     * @return Node\Expr
     */
    public function makeErrorNode($last): Node\Expr;
}
