<?php declare(strict_types=1);

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class ExpressionStatement implements StatementInterface
{
    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->lookAhead();
        $expr = $parser->getExpressionParser()->parse($parser);
        $stmt = null;
        if ($expr !== null) {
            if ($parser->eatIf(ord(';')) === null) {
                $parser->unexpected($parser->lookAhead());
            }
            $stmt = new Node\Stmt\Expression($expr, $parser->getAttributes($token, $parser->last()));
        }

        return $stmt;
    }

    public function getToken(): ?int
    {
        return null;
    }
}
