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

        return new Node\Stmt\InlineHTML($token->value, $parser->getAttributes($token, $token, ['hasLeadingNewline' => $hasLeadingNewline]));
    }

    public function getToken(): ?int
    {
        return Tokens::T_INLINE_HTML;
    }
}
