<?php declare(strict_types=1);

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class InlineHtml implements StatementInterface
{
    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();
        $hasLeadingNewline = $token->startAttributes['hasLeadingNewline'] ?? false;
        $node = new Node\Stmt\InlineHTML($token->value, ['hasLeadingNewline' => $hasLeadingNewline]);
        $parser->setAttributes($node, $token, $token);

        return $node;
    }

    public function getToken(): ?int
    {
        return Tokens::T_INLINE_HTML;
    }
}
