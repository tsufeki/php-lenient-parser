<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpLenientParser\Token;
use PhpParser\Node;

interface ExpressionParserInterface
{
    public function parse(ParserStateInterface $parser, int $precedence = 0): ?Node\Expr;

    public function parseOrError(ParserStateInterface $parser, int $precedence = 0): Node\Expr;

    /**
     * @return Node\Expr[]
     */
    public function parseList(ParserStateInterface $parser): array;

    /**
     * @param Node|Token $last Node/token preceeding error.
     */
    public function makeErrorNode($last): Node\Expr\Error;
}
