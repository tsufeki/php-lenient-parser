<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

interface InfixInterface
{
    public const LEFT_ASSOCIATIVE = 1;
    public const RIGHT_ASSOCIATIVE = 2;
    public const NOT_ASSOCIATIVE = 3;

    public function parse(ParserStateInterface $parser, Node\Expr $left): ?Node\Expr;

    public function getToken(): int;

    public function getPrecedence(): int;

    public function getAssociativity(): int;
}
