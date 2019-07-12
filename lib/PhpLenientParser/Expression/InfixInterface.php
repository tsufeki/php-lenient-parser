<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

interface InfixInterface
{
    public function parse(ParserStateInterface $parser, Node\Expr $left): ?Node\Expr;

    public function getToken(): int;

    public function getPrecedence(): int;
}
