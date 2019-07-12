<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

interface PrefixInterface
{
    public function parse(ParserStateInterface $parser): ?Node\Expr;

    public function getToken(): int;
}
