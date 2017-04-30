<?php

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

        return $parser->setAttributes(
            new Node\Stmt\InlineHTML($token->value, ['hasLeadingNewline' => $hasLeadingNewline]),
            $token, $token
        );
    }

    public function getToken()
    {
        return Tokens::T_INLINE_HTML;
    }
}
