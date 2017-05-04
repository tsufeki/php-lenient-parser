<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Parser\Tokens;

class NameSpecialPreScope extends NameSpecial
{
    public function parse(ParserStateInterface $parser)
    {
        if ($parser->lookAhead(1)->type !== Tokens::T_PAAMAYIM_NEKUDOTAYIM) {
            return null;
        }

        return parent::parse($parser);
    }
}
