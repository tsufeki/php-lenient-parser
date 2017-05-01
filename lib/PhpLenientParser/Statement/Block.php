<?php

namespace PhpLenientParser\Statement;

use PhpParser\Node;
use PhpLenientParser\ParserStateInterface;

class Block implements StatementInterface
{
    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();
        $stmts = $parser->getStatementParser()->parseList($parser, ord('}'));
        $parser->assert(ord('}'));

        $comments = $token->startAttributes['comments'] ?? [];
        if (!empty($comments)) {
            if (empty($stmts)) {
                $stmts[] = $parser->setAttributes(new Node\Stmt\Nop(), $token, $token);
            } else {
                $comments = array_merge($comments, $stmts[0]->getAttribute('comments') ?? []);
                $stmts[0]->setAttribute('comments', $comments);
            }
        }

        return $stmts;
    }

    public function getToken()
    {
        return ord('{');
    }
}
