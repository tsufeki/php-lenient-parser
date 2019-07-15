<?php declare(strict_types=1);

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class Nop implements StatementInterface
{
    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();
        $stmts = [];
        if (!empty($token->startAttributes['comments'])) {
            $stmts[] = new Node\Stmt\Nop($parser->getAttributes($token, $token));
        }

        return $stmts;
    }

    public function getToken(): ?int
    {
        return ord(';');
    }
}
