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
            $node = new Node\Stmt\Nop();
            $parser->setAttributes($node, $token, $token);
            $stmts[] = $node;
        }

        return $stmts;
    }

    public function getToken(): ?int
    {
        return ord(';');
    }
}
