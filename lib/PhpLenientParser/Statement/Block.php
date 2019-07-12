<?php declare(strict_types=1);

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

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
                $nop = new Node\Stmt\Nop();
                $parser->setAttributes($nop, $token, $token);
                $stmts[] = $nop;
            } else {
                $comments = array_merge($comments, $stmts[0]->getAttribute('comments') ?? []);
                $stmts[0]->setAttribute('comments', $comments);
            }
        }

        return $stmts;
    }

    public function getToken(): ?int
    {
        return ord('{');
    }
}
